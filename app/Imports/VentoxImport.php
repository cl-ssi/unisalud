<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\HumanName;
use App\Models\Identifier;
use App\Models\User;
use App\Models\VentoxUser;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;

class VentoxImport extends BasePatientImport
{
    public function __construct()
    {
        Log::info('=== VentoxImport INSTANCIADO ===');
    }

    public function model(array $row)
    {
        $this->ensureBaseCachesAreLoaded();
        self::$currentRow++;
        $excelRowNumber = self::$currentRow + 1;

        Log::info('=== PROCESANDO FILA VENTOX ' . $excelRowNumber . ' ===', [
            'run' => $row['run'] ?? 'sin run',
            'datos' => ($row['nombre'] ?? '') . ' ' . ($row['apellido_paterno'] ?? ''),
        ]);

        $row = array_map(function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $row);

        $headings = [
            'establecimiento',
            'nombre',
            'apellido_paterno',
            'apellido_materno',
            'run',
            'dv',
            'prevision',
            'sexo',
            'genero',
            'fecha_nacimiento',
            'nacionalidad',
            'calle',
            'numero',
            'departamento',
            'comuna',
            'telefono',
            'health_service_check',
            'medical_center_control',
            'equipment_type',
            'brand',
            'model',
            'therapy_time',
            'flow',
        ];

        foreach ($headings as $heading) {
            $row[$heading] = $row[$heading] ?? null;
        }

        if (($row['run'] ?? '') === '' || ($row['dv'] ?? '') === '') {
            self::$skippedCount++;
            $filaVacia = empty($row['nombre']) && empty($row['apellido_paterno']) && empty($row['establecimiento']);
            if (!$filaVacia) {
                Log::error('Fila ' . $excelRowNumber . ' sin RUN/DV, saltando', [
                    'contenido_completo' => $row,
                    'nombre' => $row['nombre'] ?? 'N/A',
                    'apellido_paterno' => $row['apellido_paterno'] ?? 'N/A',
                    'establecimiento' => $row['establecimiento'] ?? 'N/A',
                ]);
            }
            return null;
        }

        try {
            $ventoxUser = $this->getVentoxUser($row);

            Log::info('Fila ' . $excelRowNumber . ' procesada exitosamente', [
                'user_id' => $ventoxUser->user_id,
                'run' => $row['run'],
            ]);

            return $ventoxUser;
        } catch (\Exception $e) {
            self::$skippedCount++;
            self::$errorCount++;
            Log::error('Error procesando fila ' . $excelRowNumber, [
                'run' => $row['run'] ?? 'N/A',
                'nombre' => ($row['nombre'] ?? '') . ' ' . ($row['apellido_paterno'] ?? ''),
                'establecimiento' => $row['establecimiento'],
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile()),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    public function getUser(array $row): User
    {
        $run = $row['run'];
        $dv = $row['dv'];
        $sexo = $row['sexo'];
        $genero = $row['genero'];
        $nacionalidad = $row['nacionalidad'];
        $nombre = $row['nombre'];
        $apellido_paterno = $row['apellido_paterno'];
        $apellido_materno = $row['apellido_materno'];
        $fecha_nacimiento = $row['fecha_nacimiento'];

        $sex = self::$sexCache[$sexo] ?? null;
        $gender = self::$genderCache[$genero] ?? null;
        $nationality = self::$countriesCache[$nacionalidad] ?? null;

        $user = User::whereHas('identifiers', function ($query) use ($run) {
            $query->where('value', $run)->where('cod_con_identifier_type_id', 1);
        })->first();

        $isNew = !$user;

        if ($isNew) {
            self::$insertedCount++;
        } else {
            self::$updatedCount++;
        }

        $userOut = User::updateOrCreate(
            ['id' => $user?->id],
            [
                'active' => 1,
                'text' => trim($nombre . ' ' . $apellido_paterno . ' ' . $apellido_materno),
                'given' => $nombre,
                'fathers_family' => $apellido_paterno,
                'mothers_family' => $apellido_materno,
                'sex' => $sex,
                'gender' => $gender,
                'birthday' => $this->formatField($fecha_nacimiento, 'date'),
                'nationality_id' => $nationality,
            ]
        );

        if ($isNew) {
            Identifier::create([
                'user_id' => $userOut->id,
                'use' => 'official',
                'cod_con_identifier_type_id' => 1,
                'value' => $run,
                'dv' => $dv,
            ]);

            HumanName::create([
                'use' => 'official',
                'given' => $nombre,
                'fathers_family' => $apellido_paterno,
                'mothers_family' => $apellido_materno,
                'period_start' => now(),
                'user_id' => $userOut->id,
            ]);
        }

        if (!empty($row['calle'])) {
            $this->getAddress($row, $userOut);
        }

        return $userOut;
    }

    public function getVentoxUser(array $row): VentoxUser
    {
        $user = $this->getUser($row);

        return VentoxUser::updateOrCreate(
            ['user_id' => $user->id],
            [
                'health_service_check' => $row['health_service_check'],
                'medical_center_control' => $row['medical_center_control'],
                'equipment_type' => $row['equipment_type'],
                'brand' => $row['brand'],
                'model' => $row['model'],
                'therapy_time' => $this->formatField($row['therapy_time'], 'integer'),
                'flow' => $this->validFlow($row['flow']),
            ]
        );
    }

    private function validFlow($value): ?string
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        $trimmed = trim((string) $value);
        $allowed = ['0.1', '0.2', '0.3', '0.4', '0.5', '0.6', '0.7', '0.8', '0.9', '1', '2', '3', '4', '5'];

        return in_array($trimmed, $allowed, true) ? $trimmed : null;
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function () {
                Log::info('=== INICIO IMPORTACIÓN PACIENTES VENTOX ===');
                self::$currentRow = 0;
            },
            AfterImport::class => function () {
                Log::info('=== FIN IMPORTACIÓN VENTOX ===', [
                    'total_filas_procesadas' => self::$currentRow,
                    'insertados' => self::$insertedCount,
                    'actualizados' => self::$updatedCount,
                    'omitidos' => self::$skippedCount,
                    'errores' => self::$errorCount,
                ]);

                if (self::$errorCount > 0) {
                    Notification::make()
                        ->title('Importación Completada con Errores')
                        ->warning()
                        ->body(self::getCompletedNotificationBody())
                        ->send();
                } else {
                    Notification::make()
                        ->title('Importación Completada')
                        ->success()
                        ->body(self::getCompletedNotificationBody())
                        ->send();
                }
            },
        ];
    }

    public static function getCompletedNotificationBody(): string
    {
        $body = 'La importación de pacientes Ventox ha finalizado. ';
        $body .= self::$currentRow . ' filas procesadas. ';
        $body .= self::$insertedCount . ' insertada(s), ';
        $body .= self::$updatedCount . ' actualizada(s), ';
        $body .= self::$skippedCount . ' omitida(s)';

        if (self::$errorCount > 0) {
            $body .= ', ' . self::$errorCount . ' error(es).';
        } else {
            $body .= '.';
        }

        $message = $body;

        self::$insertedCount = 0;
        self::$updatedCount = 0;
        self::$skippedCount = 0;
        self::$errorCount = 0;
        self::$currentRow = 0;

        return $message;
    }
}
