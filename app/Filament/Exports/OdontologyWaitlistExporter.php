<?php

namespace App\Filament\Exports;

use App\Filament\Imports\OdontologyWaitlistImporter;
use App\Models\OdontologyWaitlist;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

/**
 * Column order/labels mirror the SIGTE template ("Libro1.xlsx") this
 * service consumes on re-upload — keep the two in sync.
 */
class OdontologyWaitlistExporter extends Exporter
{
    protected static ?string $model = OdontologyWaitlist::class;

    /**
     * Reverse of OdontologyWaitlistImporter::SEX_CODE_MAP. "other" predates
     * the "intersex"/"not_informed" codes and has no dedicated SIGTE code,
     * so it falls back to 99 (desconocido) like an unmapped value would.
     */
    private static function sexCode(?string $enumValue): ?string
    {
        if (blank($enumValue)) {
            return null;
        }

        static $map = null;
        $map ??= array_flip(OdontologyWaitlistImporter::SEX_CODE_MAP);

        return $map[$enumValue] ?? '99';
    }

    private static function ruralCode(?bool $isRural): ?string
    {
        return match ($isRural) {
            false => '1',
            true => '2',
            default => null,
        };
    }

    private static function viaCode(?string $via): ?string
    {
        return match ($via) {
            'calle' => '1',
            'pasaje' => '2',
            'avenida' => '3',
            'otro' => '4',
            default => null,
        };
    }

    private static function formatDate($state): ?string
    {
        return $state ? Carbon::parse($state)->format('d-m-Y') : null;
    }

    /**
     * waitlistAge is stored as a float; the SIGTE format uses a comma as
     * the decimal separator (e.g. "15,54722222"), not a period.
     */
    private static function formatAge($state): ?string
    {
        return filled($state) ? str_replace('.', ',', (string) $state) : null;
    }

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('health_service_id')
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
                ->formatStateUsing(fn ($state) => self::formatDate($state)),
            ExportColumn::make('user.sex')
                ->label('SEXO')
                ->formatStateUsing(fn ($state) => self::sexCode($state?->value)),
            ExportColumn::make('healthcareType.code')
                ->label('PREVISION'),
            ExportColumn::make('entryType.code')
                ->label('TIPO_PREST'),
            ExportColumn::make('minsalSpecialty.code')
                ->label('PRESTA_MIN'),
            ExportColumn::make('plano')
                ->label('PLANO'),
            ExportColumn::make('extremity')
                ->label('EXTREMIDAD'),
            ExportColumn::make('establishmentHealthCareService.text')
                ->label('PRESTA_EST'),
            ExportColumn::make('entry_date')
                ->label('F_ENTRADA')
                ->formatStateUsing(fn ($state) => self::formatDate($state)),
            ExportColumn::make('originEstablishment.code_deis')
                ->label('ESTAB_ORIG'),
            ExportColumn::make('destinyEstablishment.code_deis')
                ->label('ESTAB_DEST'),
            ExportColumn::make('exit_date')
                ->label('F_SALIDA')
                ->formatStateUsing(fn ($state) => self::formatDate($state)),
            ExportColumn::make('exit_code')
                ->label('C_SALIDA'),
            ExportColumn::make('referring_specialty')
                ->label('E_OTOR_AT'),
            ExportColumn::make('minsalExitSpecialty.code')
                ->label('PRESTA_MIN_SALIDA'),
            ExportColumn::make('prais')
                ->label('PRAIS'),
            ExportColumn::make('region_id')
                ->label('REGION'),
            ExportColumn::make('commune.code_deis')
                ->label('COMUNA'),
            ExportColumn::make('suspected_diagnosis')
                ->label('SOSPECHA_DIAG'),
            ExportColumn::make('confirmed_diagnosis')
                ->label('CONFIR_DIAG'),
            ExportColumn::make('user.address.city')
                ->label('CIUDAD'),
            ExportColumn::make('user.address.is_rural')
                ->label('COND_RURALIDAD')
                ->formatStateUsing(fn ($state, $record) => self::ruralCode($record->user?->address?->is_rural)),
            ExportColumn::make('user.address.via')
                ->label('VIA_DIRECCION')
                ->formatStateUsing(fn ($state, $record) => self::viaCode($record->user?->address?->via)),
            ExportColumn::make('user.address.text')
                ->label('NOM_CALLE'),
            ExportColumn::make('user.address.line')
                ->label('NUM_DIRECCION'),
            ExportColumn::make('user.address.suburb')
                ->label('RESTO_DIRECCION'),
            ExportColumn::make('user.homeContactPoint.value')
                ->label('FONO_FIJO'),
            ExportColumn::make('user.mobileContactPoint.value')
                ->label('FONO_MOVIL'),
            ExportColumn::make('user.emailContactPoint.value')
                ->label('EMAIL'),
            ExportColumn::make('appointment_date')
                ->label('F_CITACION')
                ->formatStateUsing(fn ($state) => self::formatDate($state)),
            ExportColumn::make('requestingProfessional.officialIdentifier.value')
                ->label('RUN_PROF_SOL'),
            ExportColumn::make('requestingProfessional.officialIdentifier.dv')
                ->label('DV_PROF_SOL'),
            ExportColumn::make('resolvingProfessional.officialIdentifier.value')
                ->label('RUN_PROF_RESOL'),
            ExportColumn::make('resolvingProfessional.officialIdentifier.dv')
                ->label('DV_PROF_RESOL'),
            ExportColumn::make('local_id')
                ->label('ID_LOCAL'),
            ExportColumn::make('result')
                ->label('RESULTADO'),
            ExportColumn::make('sigte_id')
                ->label('SIGTE_ID'),
            ExportColumn::make('elapsed_days')
                ->label('DIAS_PASADOS'),
            ExportColumn::make('waitlistAge')
                ->label('EDAD')
                ->formatStateUsing(fn ($state) => self::formatAge($state)),
            ExportColumn::make('waitlistYear')
                ->label('AÑO'),
            ExportColumn::make('medicalBenefit.text')
                ->label('TIPO PRESTACION'),
            ExportColumn::make('waitlistSpecialty.text')
                ->label('ESPECIALIDAD'),
            ExportColumn::make('establishment.alias')
                ->label('ESTABLECIMIENTO'),
            ExportColumn::make('worker')
                ->label('FUNCIONARIO'),
            ExportColumn::make('pediatric')
                ->label('PEDIATRICO'),
            ExportColumn::make('lb')
                ->label('LB'),
            ExportColumn::make('iqType')
                ->label('Tipo de IQ'),
            ExportColumn::make('originCommune.name')
                ->label('Comuna Origen'),
            ExportColumn::make('praisUser')
                ->label('Usuario PRAIS'),
            ExportColumn::make('lbPrais')
                ->label('LB PRAIS'),
            ExportColumn::make('lbUrinary')
                ->label('LB INCONTINENCIA URINARIA'),
            ExportColumn::make('exitError')
                ->label('Error Egreso'),
            ExportColumn::make('procedureType')
                ->label('Tipo Procedimiento'),
            ExportColumn::make('sename')
                ->label('SENAME '),
            // No source data yet for these two columns of the template —
            // kept so the exported file's column structure still matches.
            ExportColumn::make('resolutividad_placeholder')
                ->label('RESOLUTIVIDAD')
                ->state(fn () => null),
            ExportColumn::make('lb_iq_aps_placeholder')
                ->label('LB IQ APS')
                ->state(fn () => null),

            // App-specific fields, not part of the SIGTE template.
            ExportColumn::make('status')
                ->label('Estado')
                ->formatStateUsing(fn ($state) => filled($state) ? ucfirst($state) : null),
            ExportColumn::make('pase_odontologico')
                ->label('Pase Odontológico')
                ->formatStateUsing(fn ($state) => $state ? 'Sí' : 'No'),
            ExportColumn::make('visible_municipality')
                ->label('Visible Municipalidad')
                ->formatStateUsing(fn ($state) => $state ? 'Sí' : 'No'),
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
