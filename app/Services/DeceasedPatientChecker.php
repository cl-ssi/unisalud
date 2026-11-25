<?php

namespace App\Services;

use App\Models\DemandManagementWaitlist;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Checks one patient against FONASA and, when FONASA has a death date, saves it
 * on the patient and marks their Gestión Demanda cases as "fallecido".
 */
class DeceasedPatientChecker
{
    public const ALIVE = 'alive';
    public const DECEASED = 'deceased';
    public const NO_DEATH_FIELD = 'no_death_field';
    public const ERROR = 'error';

    public function __construct(protected FonasaService $fonasa = new FonasaService())
    {
    }

    /**
     * @return array{result: string, date?: string, cases?: int, error?: string}
     */
    public function check(User $user, ?int $byUserId = null): array
    {
        $identifier = $user->identifiers()->where('cod_con_identifier_type_id', 1)->orderBy('created_at')->first();
        if (! $identifier || blank($identifier->value)) {
            return ['result' => self::ERROR, 'error' => 'Paciente sin RUN'];
        }

        $beneficiary = $this->fonasa->find($identifier->value, $identifier->dv);
        if (isset($beneficiary['error'])) {
            return ['result' => self::ERROR, 'error' => $beneficiary['error']];
        }

        $date = FonasaService::deathDate($beneficiary);
        if (! $date) {
            return ['result' => FonasaService::hasDeathDateField($beneficiary) ? self::ALIVE : self::NO_DEATH_FIELD];
        }

        return ['result' => self::DECEASED, 'date' => $date, 'cases' => $this->markDeceased($user, $date, $byUserId)];
    }

    /**
     * Saves the death date on the patient and marks their cases that are not
     * "fallecido" yet; each one gets an entry in its history. Returns the
     * number of cases changed.
     */
    public function markDeceased(User $user, string $date, ?int $byUserId = null): int
    {
        return DB::transaction(function () use ($user, $date, $byUserId) {
            if (blank($user->deceased_datetime)) {
                $user->forceFill(['deceased_datetime' => $date])->save();
            }

            $cases = DemandManagementWaitlist::query()
                ->where('user_id', $user->id)
                ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '<>', 'fallecido'))
                ->get();

            $text = 'Fallecido según FONASA (fecha de defunción ' . Carbon::parse($date)->format('d-m-Y') . ')';
            foreach ($cases as $case) {
                $case->update(['status' => 'fallecido']);
                $case->events()->create([
                    'status' => 'fallecido',
                    'registered_at' => now(),
                    'text' => $text,
                    'register_user_id' => $byUserId ?? auth()->id() ?? User::query()->value('id'),
                ]);
            }

            return $cases->count();
        });
    }
}
