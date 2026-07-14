<?php

namespace App\Filament\Pages\Sigte;

use App\Models\Address;
use App\Models\ContactPoint;
use App\Models\Identifier;
use App\Models\SigteExternalWaitlistImport;
use App\Models\SigteExternalWaitlistRun;
use App\Models\SigteSurgicalWaitlist;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SigteBaseDeDatos extends Page
{
    protected static ?string $navigationGroup = 'SIGTE';
    protected static ?string $navigationLabel = 'Base de Datos';
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.sigte.sigte-base-de-datos';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('SIGTE LE QX: administrador')
            || auth()->user()?->can('be god');
    }

    protected function getViewData(): array
    {
        return [
            'lecneMeta'  => $this->getMeta('lecne'),
            'leqxMeta'   => static::getLeqxUploadMeta(),
            'lastExport' => $this->getLastExportInfo(),
        ];
    }

    private function getLastExportInfo(): ?array
    {
        $lastExportAt = SigteSurgicalWaitlist::max('exported_at');

        if (! $lastExportAt) {
            return null;
        }

        $lastExportRecord = SigteSurgicalWaitlist::with('exportedBy')
            ->where('exported_at', $lastExportAt)
            ->first();

        return [
            'fecha'     => Carbon::parse($lastExportAt)->format('d-m-Y H:i'),
            'usuario'   => $lastExportRecord?->exportedBy?->text ?: '-',
            'pacientes' => SigteSurgicalWaitlist::where('exported_at', $lastExportAt)->count(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cargarLeCne')
                ->label('Cargar LE CNE')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->modalHeading('Cargar Base de Datos LE CNE')
                ->modalDescription('Suba un archivo .xlsx en formato SIGTE (columnas: RUN, DV, NOMBRES, PRIMER_APELLIDO, SEGUNDO_APELLIDO, FECHA_NAC, SEXO, VIA_DIRECCION, NOM_CALLE, NUM_DIRECCION, RESTO_DIRECCION, CIUDAD, COND_RURALIDAD, FONO_FIJO, FONO_MOVIL, EMAIL…)')
                ->form([
                    FileUpload::make('file')
                        ->label('Archivo LE CNE (.xlsx)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->disk('local')
                        ->directory('sigte-uploads')
                        ->visibility('private')
                        ->required(),
                ])
                ->modalSubmitActionLabel('Importar')
                ->action(fn (array $data) => $this->processLeCne($data['file'])),

            Action::make('cargarLeQx')
                ->label('Cargar LE Quirúrgica')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Cargar Base de Datos LE Quirúrgica')
                ->modalDescription('Suba un archivo .xlsx en formato SIGTE con el listado actual de la Lista de Espera Quirúrgica, utilizado para detección de duplicados entre especialidades.')
                ->form([
                    FileUpload::make('file')
                        ->label('Archivo LE Quirúrgica (.xlsx)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->disk('local')
                        ->directory('sigte-uploads')
                        ->visibility('private')
                        ->required(),
                ])
                ->modalSubmitActionLabel('Importar')
                ->action(fn (array $data) => $this->processLeQx($data['file'])),
        ];
    }

    private function processLeCne(string $filePath): void
    {
        try {
            $fullPath = Storage::disk('local')->path($filePath);
            $spreadsheet = IOFactory::load($fullPath);
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

            if (empty($rows)) {
                Notification::make()->title('El archivo está vacío')->danger()->send();
                return;
            }

            $headers = array_map('trim', array_shift($rows));
            $nuevos = $actualizados = $errores = 0;

            foreach ($rows as $rowData) {
                if (!array_filter($rowData, fn ($v) => $v !== null && $v !== '')) {
                    continue;
                }
                if (count($rowData) < count($headers)) {
                    continue;
                }

                $data = array_combine($headers, array_map(fn ($v) => trim((string) ($v ?? '')), $rowData));
                $run = preg_replace('/[^0-9]/', '', $data['RUN'] ?? '');
                if (!$run) {
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

            Storage::disk('local')->delete($filePath);

            Notification::make()
                ->title("{$total} pacientes procesados")
                ->body("{$nuevos} nuevos · {$actualizados} actualizados" . ($errores ? " · {$errores} errores" : ''))
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al procesar el archivo')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function processLeQx(string $filePath): void
    {
        try {
            $fullPath = Storage::disk('local')->path($filePath);
            $spreadsheet = IOFactory::load($fullPath);
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

            if (empty($rows)) {
                Notification::make()->title('El archivo está vacío')->danger()->send();
                return;
            }

            $headers = array_map('trim', array_shift($rows));
            $runs = [];

            foreach ($rows as $rowData) {
                if (!array_filter($rowData, fn ($v) => $v !== null && $v !== '')) {
                    continue;
                }
                if (count($rowData) < count($headers)) {
                    continue;
                }

                $data = array_combine($headers, array_map(fn ($v) => trim((string) ($v ?? '')), $rowData));
                $run = preg_replace('/[^0-9]/', '', $data['RUN'] ?? '');
                if ($run) {
                    $runs[] = $run;
                }
            }

            $runs = array_values(array_unique($runs));
            $total = count($runs);

            DB::transaction(function () use ($runs, $total, $filePath) {
                // A new upload fully replaces the previous one: this is a
                // point-in-time snapshot of another specialty's waitlist,
                // not a cumulative record, so stale entries shouldn't linger.
                SigteExternalWaitlistImport::query()->delete();

                $import = SigteExternalWaitlistImport::create([
                    'uploaded_by' => auth()->id(),
                    'filename'    => basename($filePath),
                    'total_count' => $total,
                ]);

                $now = now();
                foreach (array_chunk($runs, 500) as $chunk) {
                    SigteExternalWaitlistRun::insert(array_map(fn ($run) => [
                        'sigte_external_waitlist_import_id' => $import->id,
                        'run'                                => $run,
                        'created_at'                         => $now,
                    ], $chunk));
                }
            });

            Storage::disk('local')->delete($filePath);

            Notification::make()
                ->title("{$total} registros cargados en LE Quirúrgica")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al procesar el archivo')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function upsertPatient(string $run, array $data): array
    {
        $identifier = Identifier::where('value', $run)
            ->where('cod_con_identifier_type_id', 1)
            ->first();

        $sexMap = ['1' => 'male', '2' => 'female', '3' => 'other', '9' => 'unknown'];
        $sex = $sexMap[$data['SEXO'] ?? ''] ?? null;

        $birthday = null;
        if (!empty($data['FECHA_NAC'])) {
            try {
                $birthday = Carbon::createFromFormat('d-m-Y', $data['FECHA_NAC'])->format('Y-m-d');
            } catch (\Exception) {}
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
        if (!empty($data['NOM_CALLE'])) {
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
        if ($email && !in_array($email, ['sinmail@sinmail.com', 'sin_correo@sinmail.cl', 'sin_correo@sinmail.com'])) {
            ContactPoint::updateOrCreate(
                ['user_id' => $user->id, 'system' => 'email', 'use' => 'work'],
                ['value' => $email]
            );
        }

        return [$isNew, $user];
    }

    private function getMeta(string $key): array
    {
        return static::readMeta($key);
    }

    private function saveMeta(string $key, array $data): void
    {
        Storage::disk('local')->makeDirectory('sigte');
        Storage::disk('local')->put("sigte/{$key}-meta.json", json_encode($data));
    }

    private static function readMeta(string $key): array
    {
        $raw = Storage::disk('local')->get("sigte/{$key}-meta.json");
        return $raw ? (json_decode($raw, true) ?? []) : [];
    }

    /**
     * Whether $run appears in the RUN list from the most recently uploaded
     * "LE Quirúrgica" file (another specialty's surgical waitlist export),
     * used to flag possible cross-specialty duplicates during patient search.
     */
    public static function isRunInLeqxList(string $run): bool
    {
        return SigteExternalWaitlistRun::where('run', $run)->exists();
    }

    public static function getLeqxUploadMeta(): array
    {
        $import = SigteExternalWaitlistImport::latest()->first();

        if (! $import) {
            return [];
        }

        return [
            'total'   => $import->total_count,
            'fecha'   => $import->created_at->format('d-m-Y H:i'),
            'archivo' => $import->filename,
        ];
    }
}
