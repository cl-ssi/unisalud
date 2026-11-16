<?php

namespace App\Services;

use Google\Auth\ApplicationDefaultCredentials;
use Illuminate\Support\Facades\Http;

/**
 * Starts an execution of a Cloud Run Job with its own container arguments
 * (Cloud Run Admin API v2, jobs.run). On Cloud Run the credentials come from
 * the service's service account, which needs the "Cloud Run Jobs Executor
 * With Overrides" role (roles/run.jobsExecutorWithOverrides) on the job.
 */
class CloudRunJobLauncher
{
    public static function isConfigured(): bool
    {
        return filled(config('services.cloud_run_import.project'))
            && filled(config('services.cloud_run_import.region'))
            && filled(config('services.cloud_run_import.job'));
    }

    /**
     * @param string[] $args replace the job container's args; the job's
     *                       command must be "php" so these start with "artisan"
     * @return string name of the started execution
     */
    public static function run(array $args): string
    {
        $config = config('services.cloud_run_import');
        $url = sprintf(
            'https://run.googleapis.com/v2/projects/%s/locations/%s/jobs/%s:run',
            $config['project'],
            $config['region'],
            $config['job'],
        );

        $token = ApplicationDefaultCredentials::getCredentials('https://www.googleapis.com/auth/cloud-platform')
            ->fetchAuthToken()['access_token'];

        $response = Http::withToken($token)
            ->timeout(30)
            ->post($url, [
                'overrides' => [
                    'containerOverrides' => [['args' => array_values($args)]],
                    'taskCount' => 1,
                ],
            ])
            ->throw();

        // A long-running operation; its metadata is the new execution.
        return $response->json('metadata.name') ?? $response->json('name') ?? '';
    }
}
