<?php

namespace App\Exports;

use App\Enums\AddressVia;
use App\Models\SigteSurgicalWaitlist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SigteSurgicalExport implements FromCollection, WithHeadings, WithStyles
{
    /**
     * @param  array<int>  $ids  The exact record ids to export, resolved once by
     *                           query() before any "mark as exported" bookkeeping
     *                           runs. Re-deriving the set from filters at download
     *                           time would wrongly exclude the very records the
     *                           action just marked as exported.
     */
    public function __construct(
        private readonly array $ids,
    ) {}

    /**
     * Builds the filtered query shared between the Excel export and the
     * admin page's "mark as exported" / export-batch bookkeeping, so both
     * always operate on the exact same set of records.
     *
     * Records that were already included in a previous export are always
     * excluded, so the same patient can never be exported twice.
     *
     * Supported $filters keys: desde, hasta, requesting_professional_id, status, complexity.
     */
    public static function query(array $filters): Builder
    {
        return SigteSurgicalWaitlist::query()
            ->whereNull('exported_at')
            ->when($filters['desde'] ?? null, fn (Builder $q, $v) => $q->where('entry_date', '>=', $v))
            ->when($filters['hasta'] ?? null, fn (Builder $q, $v) => $q->where('entry_date', '<=', $v))
            ->when($filters['requesting_professional_id'] ?? null, fn (Builder $q, $v) => $q->where('requesting_professional_id', $v))
            ->when($filters['status'] ?? null, fn (Builder $q, $v) => $q->where('status', $v))
            ->when($filters['complexity'] ?? null, fn (Builder $q, $v) => $q->where('complexity', $v));
    }

    public function collection(): Collection
    {
        $query = SigteSurgicalWaitlist::query()->whereIn('id', $this->ids)->with([
            'user.officialIdentifier',
            'user.address',
            'user.homeContactPoint',
            'user.mobileContactPoint',
            'user.emailContactPoint',
            'procedureCode',
            'requestingProfessional.officialIdentifier',
            'resolvingProfessional.officialIdentifier',
            'originEstablishment',
            'destinyEstablishment',
        ]);

        return $query->orderBy('entry_date')->get()->map(fn ($row) => $this->mapRow($row));
    }

    public function headings(): array
    {
        return [
            'SERV_SALUD', 'RUN', 'DV', 'NOMBRES', 'PRIMER_APELLIDO', 'SEGUNDO_APELLIDO',
            'FECHA_NAC', 'SEXO', 'PREVISION', 'TIPO_PREST',
            'PRESTA_MIN', 'PLANO', 'EXTREMIDAD', 'PRESTA_EST',
            'F_ENTRADA', 'ESTAB_ORIG', 'ESTAB_DEST',
            'F_SALIDA', 'C_SALIDA', 'E_OTOR_AT', 'PRESTA_MIN_SALIDA',
            'PRAIS', 'REGION', 'COMUNA', 'SOSPECHA_DIAG', 'CONFIR_DIAG',
            'CIUDAD', 'COND_RURALIDAD', 'VIA_DIRECCION',
            'NOM_CALLE', 'NUM_DIRECCION', 'RESTO_DIRECCION',
            'FONO_FIJO', 'FONO_MOVIL', 'EMAIL', 'F_CITACION',
            'RUN_PROF_SOL', 'DV_PROF_SOL', 'RUN_PROF_RESOL', 'DV_PROF_RESOL',
            'ID_LOCAL', 'RESULTADO', 'SIGTE_ID',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function mapRow(SigteSurgicalWaitlist $r): array
    {
        $user    = $r->user;
        $address = $user?->address;

        return [
            $r->health_service_id,
            $user?->officialIdentifier?->value,
            $user?->officialIdentifier?->dv,
            strtoupper($user?->given ?? ''),
            strtoupper($user?->fathers_family ?? ''),
            strtoupper($user?->mothers_family ?? ''),
            $user?->birthday?->format('d-m-Y'),
            match ($user?->sex?->value) {
                'male'    => 1,
                'female'  => 2,
                'other'   => 3,
                'unknown' => 9,
                default   => null,
            },
            $r->healthcare_type_id,
            4, // TIPO_PREST = Lista de espera quirúrgica
            $r->procedureCode?->code,
            $r->plano,
            $r->extremity,
            $r->procedureCode?->text,
            $r->entry_date?->format('d-m-Y'),
            $r->originEstablishment?->alias ?: $r->origin_establishment_id,
            $r->destinyEstablishment?->alias ?: $r->destiny_establishment_id,
            null, // F_SALIDA
            null, // C_SALIDA
            $r->referring_specialty,
            null, // PRESTA_MIN_SALIDA
            $r->prais ? 1 : 2,
            $r->region_id,
            $r->commune_id,
            $r->suspected_diagnosis,
            $r->confirmed_diagnosis,
            $address?->city,
            $address?->is_rural ? 2 : ($address ? 1 : null), // 1=Urbano, 2=Rural
            match ($address?->via) {
                'calle'   => 1,
                'pasaje'  => 2,
                'avenida' => 3,
                'otro'    => 4,
                default   => null,
            },
            $address?->text,   // NOM_CALLE (stored as 'text' in Address)
            $address?->line,   // NUM_DIRECCION
            $address?->suburb, // RESTO_DIRECCION
            $user?->homeContactPoint?->value,
            $user?->mobileContactPoint?->value,
            $user?->emailContactPoint?->value,
            null, // F_CITACION
            $r->requestingProfessional?->officialIdentifier?->value,
            $r->requestingProfessional?->officialIdentifier?->dv,
            $r->resolvingProfessional?->officialIdentifier?->value,
            $r->resolvingProfessional?->officialIdentifier?->dv,
            $r->identifier,
            null, // RESULTADO
            $r->sigte_id,
        ];
    }
}
