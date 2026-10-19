<?php

namespace App\Exports;

use App\Imports\DemandManagementWaitlistImport;
use Carbon\Carbon;
use pxlrbt\FilamentExcel\Columns\Column;

/**
 * Column definitions for the demand management waitlist filament-excel export.
 *
 * Column order/labels mirror the SIGTE template ("Libro1.xlsx") that
 * DemandManagementWaitlistImport consumes on re-upload — keep the two in sync.
 */
class DemandManagementWaitlistExport
{
    /**
     * Relations read by the columns below; eager loaded so the export
     * doesn't run dozens of queries per row.
     */
    public const RELATIONS = [
        'user.officialIdentifier',
        'user.address',
        'user.homeContactPoint',
        'user.mobileContactPoint',
        'user.emailContactPoint',
        'healthcareType',
        'entryType',
        'minsalSpecialty',
        'minsalExitSpecialty',
        'establishmentHealthCareService',
        'originEstablishment',
        'destinyEstablishment',
        'commune',
        'originCommune',
        'requestingProfessional.officialIdentifier',
        'resolvingProfessional.officialIdentifier',
        'medicalBenefit',
        'waitlistSpecialty',
        'establishment',
    ];

    /**
     * Reverse of DemandManagementWaitlistImport::SEX_CODE_MAP. "other" predates
     * the "intersex"/"not_informed" codes and has no dedicated SIGTE code,
     * so it falls back to 99 (desconocido) like an unmapped value would.
     */
    private static function sexCode(?string $enumValue): ?string
    {
        if (blank($enumValue)) {
            return null;
        }

        static $map = null;
        $map ??= array_flip(DemandManagementWaitlistImport::SEX_CODE_MAP);

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

    private static function date(string $name, string $heading): Column
    {
        return Column::make($name)
            ->heading($heading)
            ->getStateUsing(fn ($record) => self::formatDate(data_get($record, $name)));
    }

    private static function yesNo(string $name, string $heading): Column
    {
        return Column::make($name)
            ->heading($heading)
            ->getStateUsing(fn ($record) => $record->{$name} ? 'Sí' : 'No');
    }

    public static function columns(): array
    {
        return [
            Column::make('health_service_id')->heading('SERV_SALUD'),
            Column::make('user.officialIdentifier.value')->heading('RUN'),
            Column::make('user.officialIdentifier.dv')->heading('DV'),
            Column::make('user.given')->heading('NOMBRES'),
            Column::make('user.fathers_family')->heading('PRIMER_APELLIDO'),
            Column::make('user.mothers_family')->heading('SEGUNDO_APELLIDO'),
            self::date('user.birthday', 'FECHA_NAC'),
            Column::make('user.sex')
                ->heading('SEXO')
                ->getStateUsing(fn ($record) => self::sexCode($record->user?->sex?->value)),
            Column::make('healthcareType.code')->heading('PREVISION'),
            Column::make('entryType.code')->heading('TIPO_PREST'),
            Column::make('minsalSpecialty.code')->heading('PRESTA_MIN'),
            Column::make('plano')->heading('PLANO'),
            Column::make('extremity')->heading('EXTREMIDAD'),
            Column::make('establishmentHealthCareService.text')->heading('PRESTA_EST'),
            self::date('entry_date', 'F_ENTRADA'),
            Column::make('originEstablishment.code_deis')->heading('ESTAB_ORIG'),
            Column::make('destinyEstablishment.code_deis')->heading('ESTAB_DEST'),
            self::date('exit_date', 'F_SALIDA'),
            Column::make('exit_code')->heading('C_SALIDA'),
            Column::make('referring_specialty')->heading('E_OTOR_AT'),
            Column::make('minsalExitSpecialty.code')->heading('PRESTA_MIN_SALIDA'),
            Column::make('prais')->heading('PRAIS'),
            Column::make('region_id')->heading('REGION'),
            Column::make('commune.code_deis')->heading('COMUNA'),
            Column::make('suspected_diagnosis')->heading('SOSPECHA_DIAG'),
            Column::make('confirmed_diagnosis')->heading('CONFIR_DIAG'),
            Column::make('user.address.city')->heading('CIUDAD'),
            Column::make('user.address.is_rural')
                ->heading('COND_RURALIDAD')
                ->getStateUsing(fn ($record) => self::ruralCode($record->user?->address?->is_rural)),
            Column::make('user.address.via')
                ->heading('VIA_DIRECCION')
                ->getStateUsing(fn ($record) => self::viaCode($record->user?->address?->via)),
            Column::make('user.address.text')->heading('NOM_CALLE'),
            Column::make('user.address.line')->heading('NUM_DIRECCION'),
            Column::make('user.address.suburb')->heading('RESTO_DIRECCION'),
            Column::make('user.homeContactPoint.value')->heading('FONO_FIJO'),
            Column::make('user.mobileContactPoint.value')->heading('FONO_MOVIL'),
            Column::make('user.emailContactPoint.value')->heading('EMAIL'),
            self::date('appointment_date', 'F_CITACION'),
            Column::make('requestingProfessional.officialIdentifier.value')->heading('RUN_PROF_SOL'),
            Column::make('requestingProfessional.officialIdentifier.dv')->heading('DV_PROF_SOL'),
            Column::make('resolvingProfessional.officialIdentifier.value')->heading('RUN_PROF_RESOL'),
            Column::make('resolvingProfessional.officialIdentifier.dv')->heading('DV_PROF_RESOL'),
            Column::make('local_id')->heading('ID_LOCAL'),
            Column::make('result')->heading('RESULTADO'),
            Column::make('sigte_id')->heading('SIGTE_ID'),
            Column::make('elapsed_days')->heading('DIAS_PASADOS'),
            Column::make('waitlistAge')
                ->heading('EDAD')
                ->getStateUsing(fn ($record) => self::formatAge($record->waitlistAge)),
            Column::make('waitlistYear')->heading('AÑO'),
            Column::make('medicalBenefit.text')->heading('TIPO PRESTACION'),
            Column::make('waitlistSpecialty.text')->heading('ESPECIALIDAD'),
            Column::make('establishment.alias')->heading('ESTABLECIMIENTO'),
            Column::make('worker')->heading('FUNCIONARIO'),
            Column::make('pediatric')->heading('PEDIATRICO'),
            Column::make('lb')->heading('LB'),
            Column::make('iqType')->heading('Tipo de IQ'),
            Column::make('originCommune.name')->heading('Comuna Origen'),
            Column::make('praisUser')->heading('Usuario PRAIS'),
            Column::make('lbPrais')->heading('LB PRAIS'),
            Column::make('lbUrinary')->heading('LB INCONTINENCIA URINARIA'),
            Column::make('exitError')->heading('Error Egreso'),
            Column::make('procedureType')->heading('Tipo Procedimiento'),
            Column::make('sename')->heading('SENAME '),
            // No source data yet for these two columns of the template —
            // kept so the exported file's column structure still matches.
            Column::make('resolutividad_placeholder')
                ->heading('RESOLUTIVIDAD')
                ->getStateUsing(fn () => null),
            Column::make('lb_iq_aps_placeholder')
                ->heading('LB IQ APS')
                ->getStateUsing(fn () => null),

            // App-specific fields, not part of the SIGTE template.
            Column::make('status')
                ->heading('Estado')
                ->getStateUsing(fn ($record) => filled($record->status) ? ucfirst($record->status) : null),
            self::yesNo('visible_municipality', 'Visible Municipalidad'),
        ];
    }
}
