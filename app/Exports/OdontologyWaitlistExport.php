<?php

namespace App\Exports;

use App\Imports\OdontologyWaitlistImport;
use BackedEnum;
use Carbon\Carbon;
use Closure;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Odontology waitlist XLSX export.
 *
 * Column order/labels mirror the SIGTE template ("Libro1.xlsx") that
 * OdontologyWaitlistImport consumes on re-upload — keep the two in sync.
 *
 * Written with OpenSpout instead of filament-excel: filament-excel resolves
 * every cell through the container and PhpSpreadsheet holds the whole sheet
 * in memory, which made large exports exceed max_execution_time.
 */
class OdontologyWaitlistExport
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
        'sigteProcedure',
        'exitSigteProcedure',
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
     * Rows fetched per query; each chunk re-runs every eager load in RELATIONS.
     */
    public const CHUNK_SIZE = 1000;

    /**
     * Writes every row of $query (the table's filtered/sorted query) to an
     * XLSX file and returns it as a download.
     */
    public static function download(Builder $query): BinaryFileResponse
    {
        $columns = self::columns();
        $path = tempnam(sys_get_temp_dir(), 'odontology-export-');

        // Shared strings, like the files OdontologyWaitlistImport reads back.
        $options = new Options();
        $options->SHOULD_USE_INLINE_STRINGS = false;

        $writer = new Writer($options);
        $writer->openToFile($path);
        $writer->getCurrentSheet()->setName('Export');
        $writer->addRow(Row::fromValues(array_keys($columns)));

        $query->with(self::RELATIONS)->chunk(self::CHUNK_SIZE, function ($records) use ($writer, $columns) {
            foreach ($records as $record) {
                $writer->addRow(Row::fromValues(array_map(
                    fn (Closure $state) => self::cellValue($state($record)),
                    array_values($columns),
                )));
            }
        });

        $writer->close();

        return response()
            ->download($path, 'lista-espera-odontologia-' . date('Y-m-d_H-i-s') . '.xlsx')
            ->deleteFileAfterSend();
    }

    /**
     * Matches the cells the previous filament-excel/PhpSpreadsheet export
     * produced: enums by label, empty values as blank cells, and numeric
     * strings as numbers unless they have a leading zero.
     */
    private static function cellValue(mixed $state): int|float|string|null
    {
        if ($state instanceof HasLabel) {
            $state = $state->getLabel();
        } elseif ($state instanceof BackedEnum) {
            $state = $state->value;
        }

        $state = (string) $state;

        if ($state === '') {
            return null;
        }

        if (is_numeric($state) && ! preg_match('/^[+-]?0\d/', $state)) {
            return $state + 0;
        }

        return $state;
    }

    /**
     * Reverse of OdontologyWaitlistImport::SEX_CODE_MAP. "other" predates
     * the "intersex"/"not_informed" codes and has no dedicated SIGTE code,
     * so it falls back to 99 (desconocido) like an unmapped value would.
     */
    private static function sexCode(?string $enumValue): ?string
    {
        if (blank($enumValue)) {
            return null;
        }

        static $map = null;
        $map ??= array_flip(OdontologyWaitlistImport::SEX_CODE_MAP);

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

    private static function value(string $name): Closure
    {
        return fn ($record) => data_get($record, $name);
    }

    private static function date(string $name): Closure
    {
        return fn ($record) => self::formatDate(data_get($record, $name));
    }

    private static function yesNo(string $name): Closure
    {
        return fn ($record) => $record->{$name} ? 'Sí' : 'No';
    }

    /**
     * @return array<string, Closure> heading => fn ($record) => cell state
     */
    public static function columns(): array
    {
        return [
            'SERV_SALUD' => self::value('health_service_id'),
            'RUN' => self::value('user.officialIdentifier.value'),
            'DV' => self::value('user.officialIdentifier.dv'),
            'NOMBRES' => self::value('user.given'),
            'PRIMER_APELLIDO' => self::value('user.fathers_family'),
            'SEGUNDO_APELLIDO' => self::value('user.mothers_family'),
            'FECHA_NAC' => self::date('user.birthday'),
            'SEXO' => fn ($record) => self::sexCode($record->user?->sex?->value),
            'PREVISION' => self::value('healthcareType.code'),
            'TIPO_PREST' => self::value('entryType.code'),
            // A specialty, or a procedure when TIPO_PREST = 3.
            'PRESTA_MIN' => fn ($record) => $record->minsalSpecialty?->code ?? $record->sigteProcedure?->code,
            'PLANO' => self::value('plano'),
            'EXTREMIDAD' => self::value('extremity'),
            'PRESTA_EST' => self::value('establishmentHealthCareService.text'),
            'F_ENTRADA' => self::date('entry_date'),
            'ESTAB_ORIG' => self::value('originEstablishment.code_deis'),
            'ESTAB_DEST' => self::value('destinyEstablishment.code_deis'),
            'F_SALIDA' => self::date('exit_date'),
            'C_SALIDA' => self::value('exit_code'),
            'E_OTOR_AT' => self::value('referring_specialty'),
            'PRESTA_MIN_SALIDA' => fn ($record) => $record->minsalExitSpecialty?->code ?? $record->exitSigteProcedure?->code,
            'PRAIS' => self::value('prais'),
            'REGION' => self::value('region_id'),
            'COMUNA' => self::value('commune.code_deis'),
            'SOSPECHA_DIAG' => self::value('suspected_diagnosis'),
            'CONFIR_DIAG' => self::value('confirmed_diagnosis'),
            'CIUDAD' => self::value('user.address.city'),
            'COND_RURALIDAD' => fn ($record) => self::ruralCode($record->user?->address?->is_rural),
            'VIA_DIRECCION' => fn ($record) => self::viaCode($record->user?->address?->via),
            'NOM_CALLE' => self::value('user.address.text'),
            'NUM_DIRECCION' => self::value('user.address.line'),
            'RESTO_DIRECCION' => self::value('user.address.suburb'),
            'FONO_FIJO' => self::value('user.homeContactPoint.value'),
            'FONO_MOVIL' => self::value('user.mobileContactPoint.value'),
            'EMAIL' => self::value('user.emailContactPoint.value'),
            'F_CITACION' => self::date('appointment_date'),
            'RUN_PROF_SOL' => self::value('requestingProfessional.officialIdentifier.value'),
            'DV_PROF_SOL' => self::value('requestingProfessional.officialIdentifier.dv'),
            'RUN_PROF_RESOL' => self::value('resolvingProfessional.officialIdentifier.value'),
            'DV_PROF_RESOL' => self::value('resolvingProfessional.officialIdentifier.dv'),
            'ID_LOCAL' => self::value('local_id'),
            'RESULTADO' => self::value('result'),
            'SIGTE_ID' => self::value('sigte_id'),
            'DIAS_PASADOS' => self::value('elapsed_days'),
            'EDAD' => fn ($record) => self::formatAge($record->waitlistAge),
            'AÑO' => self::value('waitlistYear'),
            'TIPO PRESTACION' => self::value('medicalBenefit.text'),
            'ESPECIALIDAD' => self::value('waitlistSpecialty.text'),
            'ESTABLECIMIENTO' => self::value('establishment.alias'),
            'FUNCIONARIO' => self::value('worker'),
            'PEDIATRICO' => self::value('pediatric'),
            'LB' => self::value('lb'),
            'Tipo de IQ' => self::value('iqType'),
            'Comuna Origen' => self::value('originCommune.name'),
            'Usuario PRAIS' => self::value('praisUser'),
            'LB PRAIS' => self::value('lbPrais'),
            'LB INCONTINENCIA URINARIA' => self::value('lbUrinary'),
            'Error Egreso' => self::value('exitError'),
            'Tipo Procedimiento' => self::value('procedureType'),
            'SENAME ' => self::value('sename'),
            // No source data yet for these two columns of the template —
            // kept so the exported file's column structure still matches.
            'RESOLUTIVIDAD' => fn () => null,
            'LB IQ APS' => fn () => null,

            // App-specific fields, not part of the SIGTE template.
            'Estado' => fn ($record) => filled($record->status) ? ucfirst($record->status) : null,
            'Pase Odontológico' => self::yesNo('pase_odontologico'),
            'Visible Municipalidad' => self::yesNo('visible_municipality'),
        ];
    }
}
