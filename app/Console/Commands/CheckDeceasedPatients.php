<?php

namespace App\Console\Commands;

use App\Models\DemandManagementWaitlist;
use App\Models\User;
use App\Services\DeceasedPatientChecker;
use App\Services\FonasaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Checks the patients of open Gestión Demanda cases against FONASA (through
 * WSSSI_URL) and marks the deceased ones. Patients already deceased or whose
 * cases are all "fallecido" are skipped. It resumes where the previous run
 * stopped, so it can be run in batches (--limit) until it reports the end.
 */
class CheckDeceasedPatients extends Command
{
    protected $signature = 'waitlist:check-deceased
        {--limit=500 : Patients to check in this run (0 = all)}
        {--sleep=200 : Pause between FONASA calls, in milliseconds}
        {--all-cases : Include patients whose cases are all closed}
        {--restart : Start again from the first patient}
        {--dry-run : Only report, change nothing}
        {--run= : Check a single RUN (e.g. 12345678-9)}';

    protected $description = 'Mark Gestión Demanda patients deceased according to FONASA';

    private const CURSOR_KEY = 'waitlist:check-deceased:last-user-id';

    public function handle(FonasaService $fonasa): int
    {
        if (! FonasaService::isConfigured()) {
            $this->error('WSSSI_URL is not configured.');

            return self::FAILURE;
        }

        $checker = new DeceasedPatientChecker($fonasa);

        if ($run = $this->option('run')) {
            return $this->checkSingleRun($fonasa, $checker, $run);
        }

        if ($this->option('restart')) {
            Cache::forget(self::CURSOR_KEY);
        }
        $afterId = (int) Cache::get(self::CURSOR_KEY, 0);
        $limit = max(0, (int) $this->option('limit'));
        $sleepMs = max(0, (int) $this->option('sleep'));
        $dryRun = (bool) $this->option('dry-run');

        $users = User::query()
            ->whereNull('deceased_datetime')
            ->where('id', '>', $afterId)
            ->whereHas('demandManagementWaitlists', function ($q) {
                $q->where(fn ($s) => $s->whereNull('status')->orWhere('status', '<>', 'fallecido'));
                if (! $this->option('all-cases')) {
                    $q->open();
                }
            })
            ->orderBy('id')
            ->when($limit > 0, fn ($q) => $q->limit($limit))
            ->get();

        if ($users->isEmpty()) {
            $this->info($afterId ? 'Nothing left to check. Use --restart to start over.' : 'No patients to check.');
            Cache::forget(self::CURSOR_KEY);

            return self::SUCCESS;
        }

        $counts = [DeceasedPatientChecker::ALIVE => 0, DeceasedPatientChecker::DECEASED => 0, DeceasedPatientChecker::NO_DEATH_FIELD => 0, DeceasedPatientChecker::ERROR => 0];
        $cases = 0;
        $bar = $this->output->createProgressBar($users->count());

        foreach ($users as $user) {
            $result = $dryRun ? $this->dryCheck($fonasa, $user) : $checker->check($user);
            $counts[$result['result']]++;
            $cases += $result['cases'] ?? 0;

            if ($result['result'] === DeceasedPatientChecker::DECEASED) {
                $this->newLine();
                $this->line("  Fallecido: user {$user->id} ({$result['date']})" . ($dryRun ? ' [dry-run]' : ", {$result['cases']} caso(s)"));
            }
            if ($counts[DeceasedPatientChecker::NO_DEATH_FIELD] >= 5 && $counts[DeceasedPatientChecker::ALIVE] === 0 && $counts[DeceasedPatientChecker::DECEASED] === 0) {
                $this->newLine();
                $this->error('The WSSSI response has no fechaFallecimiento field: the proxy must return it. Stopping.');

                return self::FAILURE;
            }

            if (! $dryRun) {
                Cache::forever(self::CURSOR_KEY, $user->id);
            }
            $bar->advance();
            usleep($sleepMs * 1000);
        }
        $bar->finish();
        $this->newLine(2);

        $this->table(['Checked', 'Alive', 'Deceased', 'Cases marked', 'Errors'], [[
            $users->count(), $counts['alive'], $counts['deceased'], $cases, $counts['error'],
        ]]);
        if ($limit > 0 && $users->count() === $limit) {
            $this->comment('More patients left: run the command again to continue.');
        }

        return self::SUCCESS;
    }

    private function checkSingleRun(FonasaService $fonasa, DeceasedPatientChecker $checker, string $run): int
    {
        [$number, $dv] = array_pad(explode('-', str_replace(['.', ' '], '', $run)), 2, null);
        $beneficiary = $fonasa->find($number, $dv);
        $this->line(json_encode($beneficiary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if (isset($beneficiary['error'])) {
            return self::FAILURE;
        }
        if (! FonasaService::hasDeathDateField($beneficiary)) {
            $this->warn('The response has no fechaFallecimiento field.');
        }

        $date = FonasaService::deathDate($beneficiary);
        $this->info($date ? "Fallecido: {$date}" : 'Sin fecha de defunción.');

        if ($date && ! $this->option('dry-run')) {
            $user = User::whereHas('identifiers', fn ($q) => $q->where('cod_con_identifier_type_id', 1)->where('value', (int) $number))->first();
            if ($user) {
                $this->info($checker->markDeceased($user, $date) . ' caso(s) marcados como fallecido.');
            }
        }

        return self::SUCCESS;
    }

    private function dryCheck(FonasaService $fonasa, User $user): array
    {
        $identifier = $user->identifiers()->where('cod_con_identifier_type_id', 1)->first();
        if (! $identifier) {
            return ['result' => DeceasedPatientChecker::ERROR];
        }
        $beneficiary = $fonasa->find($identifier->value, $identifier->dv);
        if (isset($beneficiary['error'])) {
            return ['result' => DeceasedPatientChecker::ERROR];
        }
        $date = FonasaService::deathDate($beneficiary);
        if ($date) {
            return ['result' => DeceasedPatientChecker::DECEASED, 'date' => $date, 'cases' => 0];
        }

        return ['result' => FonasaService::hasDeathDateField($beneficiary) ? DeceasedPatientChecker::ALIVE : DeceasedPatientChecker::NO_DEATH_FIELD];
    }
}
