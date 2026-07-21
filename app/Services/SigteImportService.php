<?php

namespace App\Services;

use App\Models\Address;
use App\Models\ContactPoint;
use App\Models\Identifier;
use App\Models\SigteExternalWaitlistImport;
use App\Models\SigteExternalWaitlistRun;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

/**
 * Parses and imports the SIGTE "LE CNE" / "LE Quirúrgica" spreadsheet
 * uploads. Runs inside a queued job (large files can take minutes to
 * process), so it must not depend on any Livewire/Filament page state.
 */
class SigteImportService
{
    /**
     * Loads a spreadsheet's rows, supporting both .xlsx/.xls and .csv
     * (semicolon-delimited, as commonly exported locally) uploads.
     */
    public function loadRows(string $filePath): array
    {
        $fullPath = Storage::disk('local')->path($filePath);

        if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'csv') {
            $reader = new Csv();
            $reader->setDelimiter(';');
            $reader->setEnclosure('"');

            $contents = file_get_contents($fullPath);
            if ($contents !== false && ! mb_check_encoding($contents, 'UTF-8')) {
                $reader->setInputEncoding('Windows-1252');
            }

            $spreadsheet = $reader->load($fullPath);
        } else {
            $spreadsheet = IOFactory::load($fullPath);
        }

        return $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
    }

    public function processLeCne(string $filePath): array
    {
        $rows = $this->loadRows($filePath);

        if (empty($rows)) {
            return ['empty' => true];
        }

        $headers = array_map('trim', array_shift($rows));
        $nuevos = $actualizados = $errores = 0;

        foreach ($rows as $rowData) {
            if (! array_filter($rowData, fn ($v) => $v !== null && $v !== '')) {
                continue;
            }
            if (count($rowData) < count($headers)) {
                continue;
            }

            $data = array_combine($headers, array_map(fn ($v) => trim((string) ($v ?? '')), $rowData));
            $run = preg_replace('/[^0-9]/', '', $data['RUN'] ?? '');
            if (! $run) {
                continue;
            }

            try {
                [$isNew] = $this->upsertPatient($run, $data);
                $isNew ? $nuevos++ : $actualizados++;
            } catch (\Exception) {
                $errores++;
            }
        }

        $total = $nuevos + $actualizados;

        $this->saveMeta('lecne', [
            'total'        => $total,
            'nuevos'       => $nuevos,
            'actualizados' => $actualizados,
            'errores'      => $errores,
            'fecha'        => now()->format('d-m-Y H:i'),
            'archivo'      => basename($filePath),
        ]);

        return compact('total', 'nuevos', 'actualizados', 'errores');
    }

    public function processLeQx(string $filePath, ?int $uploadedBy): array
    {
        $rows = $this->loadRows($filePath);

        if (empty($rows)) {
            return ['empty' => true];
        }

        $headers = array_map('trim', array_shift($rows));
        $records = [];

        foreach ($rows as $rowData) {
            if (! array_filter($rowData, fn ($v) => $v !== null && $v !== '')) {
                continue;
            }
            if (count($rowData) < count($headers)) {
                continue;
            }

            $data = array_combine($headers, array_map(fn ($v) => trim((string) ($v ?? '')), $rowData));
            $run = preg_replace('/[^0-9]/', '', $data['RUN'] ?? '');
            if ($run) {
                // Later rows for the same RUN overwrite earlier ones.
                $records[$run] = $data;
            }
        }

        $total = count($records);

        DB::transaction(function () use ($records, $total, $filePath, $uploadedBy) {
            // A new upload fully replaces the previous one: this is a
            // point-in-time snapshot of another specialty's waitlist,
            // not a cumulative record, so stale entries shouldn't linger.
            SigteExternalWaitlistImport::query()->delete();

            $import = SigteExternalWaitlistImport::create([
                'uploaded_by' => $uploadedBy,
                'filename'    => basename($filePath),
                'total_count' => $total,
            ]);

            $now = now();
            foreach (array_chunk($records, 500, true) as $chunk) {
                SigteExternalWaitlistRun::insert(array_map(fn ($run, $data) => [
                    'sigte_external_waitlist_import_id' => $import->id,
                    'run'                                => $run,
                    'data'                               => json_encode($data),
                    'created_at'                         => $now,
                ], array_keys($chunk), $chunk));
            }
        });

        return compact('total');
    }

    private function upsertPatient(string $run, array $data): array
    {
        $identifier = Identifier::where('value', $run)
            ->where('cod_con_identifier_type_id', 1)
            ->first();

        $sexMap = ['1' => 'male', '2' => 'female', '3' => 'other', '9' => 'unknown'];
        $sex = $sexMap[$data['SEXO'] ?? ''] ?? null;

        $birthday = null;
        if (! empty($data['FECHA_NAC'])) {
            try {
                // The export stores dates as US-style m/d/Y, e.g. "1/25/2004".
                $birthday = Carbon::createFromFormat('n/j/Y', $data['FECHA_NAC'])->format('Y-m-d');
            } catch (\Exception) {
            }
        }

        if (! $identifier?->user_id) {
            $given         = $data['NOMBRES'] ?: null;
            $fathersFamily = $data['PRIMER_APELLIDO'] ?: null;
            $mothersFamily = $data['SEGUNDO_APELLIDO'] ?: null;

            $user = User::create([
                'active'         => 1,
                'given'          => $given,
                'fathers_family' => $fathersFamily,
                'mothers_family' => $mothersFamily,
                'text'           => trim("{$given} {$fathersFamily} {$mothersFamily}"),
                'birthday'       => $birthday,
                'sex'            => $sex,
            ]);

            Identifier::create([
                'user_id'                    => $user->id,
                'use'                        => 'official',
                'cod_con_identifier_type_id' => 1,
                'value'                      => $run,
                'dv'                         => $data['DV'] ?: null,
            ]);

            $isNew = true;
        } else {
            $user = User::find($identifier->user_id);

            $updates = array_filter([
                'given'          => $user->given          ?: ($data['NOMBRES'] ?: null),
                'fathers_family' => $user->fathers_family ?: ($data['PRIMER_APELLIDO'] ?: null),
                'mothers_family' => $user->mothers_family ?: ($data['SEGUNDO_APELLIDO'] ?: null),
                'birthday'       => $user->birthday       ?: $birthday,
                'sex'            => $user->sex            ?: $sex,
                'text'           => $user->text           ?: trim(($data['NOMBRES'] ?? '') . ' ' . ($data['PRIMER_APELLIDO'] ?? '') . ' ' . ($data['SEGUNDO_APELLIDO'] ?? '')),
            ]);

            if ($updates) {
                $user->update($updates);
            }

            $isNew = false;
        }

        $viaMap = ['1' => 'calle', '2' => 'pasaje', '3' => 'avenida', '4' => 'otro'];
        if (! empty($data['NOM_CALLE'])) {
            Address::updateOrCreate(
                ['user_id' => $user->id, 'use' => 'home'],
                array_filter([
                    'type'     => 'physical',
                    'text'     => $data['NOM_CALLE'] ?: null,
                    'line'     => $data['NUM_DIRECCION'] ?: null,
                    'suburb'   => $data['RESTO_DIRECCION'] ?: null,
                    'city'     => $data['CIUDAD'] ?: null,
                    'is_rural' => ($data['COND_RURALIDAD'] ?? '') === '2',
                    'via'      => $viaMap[$data['VIA_DIRECCION'] ?? ''] ?? null,
                ])
            );
        }

        foreach ([['phone', 'home', 'FONO_FIJO'], ['phone', 'mobile', 'FONO_MOVIL']] as [$sys, $use, $col]) {
            $value = $data[$col] ?? '';
            if ($value && $value !== '0') {
                ContactPoint::updateOrCreate(
                    ['user_id' => $user->id, 'system' => $sys, 'use' => $use],
                    ['value' => $value]
                );
            }
        }

        $email = $data['EMAIL'] ?? '';
        if ($email && ! in_array($email, ['sinmail@sinmail.com', 'sin_correo@sinmail.cl', 'sin_correo@sinmail.com'])) {
            ContactPoint::updateOrCreate(
                ['user_id' => $user->id, 'system' => 'email', 'use' => 'work'],
                ['value' => $email]
            );
        }

        return [$isNew, $user];
    }

    public function saveMeta(string $key, array $data): void
    {
        Storage::disk('local')->makeDirectory('sigte');
        Storage::disk('local')->put("sigte/{$key}-meta.json", json_encode($data));
    }

    public function readMeta(string $key): array
    {
        $raw = Storage::disk('local')->get("sigte/{$key}-meta.json");

        return $raw ? (json_decode($raw, true) ?? []) : [];
    }
}
