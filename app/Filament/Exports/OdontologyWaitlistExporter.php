<?php

namespace App\Filament\Exports;

use App\Models\OdontologyWaitlist;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class OdontologyWaitlistExporter extends Exporter
{
    protected static ?string $model = OdontologyWaitlist::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('healthService.name')
                ->label('SERV_SALUD'),
            ExportColumn::make('user.officialIdentifier.value')
                ->label('RUN'),
            ExportColumn::make('user.officialIdentifier.dv')
                ->label('DV'),
            ExportColumn::make('user.given')
                ->label('NOMBRES'),
            ExportColumn::make('user.fathers_family')
                ->label('PRIMER_APELLIDO'),
            ExportColumn::make('user.mothers_family')
                ->label('SEGUNDO_APELLIDO'),
            ExportColumn::make('user.birthday')
                ->label('FECHA_NAC')
                ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->format('d/m/Y') : null),
            ExportColumn::make('healthcareType.code')
                ->label('TIPO_PREST'),
            ExportColumn::make('minsalSpecialty.code')
                ->label('PRESTA_MIN'),
            ExportColumn::make('plano')
                ->label('PLANO')
                ->formatStateUsing(fn($state) => filled($state) ? $state : null),
            ExportColumn::make('extremity')
                ->label('EXTREMIDAD')
                ->formatStateUsing(fn($state) => filled($state) ? $state : null),
            ExportColumn::make('entry_date')
                ->label('F_ENTRADA')
                ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->format('d/m/Y') : null),
            ExportColumn::make('originEstablishment.code_deis')
                ->label('ESTAB_ORIG'),
            ExportColumn::make('destinyEstablishment.code_deis')
                ->label('ESTAB_DEST'),
            ExportColumn::make('exit_date')
                ->label('F_SALIDA')
                ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->format('d/m/Y') : null),


            ExportColumn::make('wait_health_care_service_id'),
            ExportColumn::make('entry_date'),
            ExportColumn::make('originEstablishment.name'),
            ExportColumn::make('destinyEstablishment.name'),
            ExportColumn::make('exit_date'),
            ExportColumn::make('exit_code'),
            ExportColumn::make('referring_specialty'),
            ExportColumn::make('exit_minsal_specialty_id'),
            ExportColumn::make('prais'),
            ExportColumn::make('region.name'),
            ExportColumn::make('commune.name'),
            ExportColumn::make('suspected_diagnosis'),
            ExportColumn::make('confirmed_diagnosis'),
            ExportColumn::make('appointment_date'),
            ExportColumn::make('requestingProfessional.id'),
            ExportColumn::make('resolvingProfessional.id'),
            ExportColumn::make('sigte_id'),

            ExportColumn::make('specialty_id'),
            ExportColumn::make('establishment.name'),
            ExportColumn::make('pediatric'),
            ExportColumn::make('lb'),
            ExportColumn::make('status'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('deleted_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your odontology waitlist export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
