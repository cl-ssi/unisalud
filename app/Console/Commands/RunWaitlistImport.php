<?php

namespace App\Console\Commands;

use App\Imports\DemandManagementWaitlistImport;
use App\Imports\OdontologyWaitlistImport;
use App\Jobs\ImportWaitlistJob;
use Illuminate\Console\Command;

/**
 * Runs one waitlist upload in the foreground. In production this is the
 * command of the Cloud Run Job started by ImportWaitlistJob::start(): the job
 * has full CPU, a long task timeout and no retries, unlike a Cloud Tasks
 * request, which is cut off after at most 30 minutes and then redelivered.
 */
class RunWaitlistImport extends Command
{
    protected $signature = 'waitlist:import
        {importClass : DemandManagementWaitlistImport or OdontologyWaitlistImport}
        {filePath : Path of the upload on the uploads disk}
        {userId : Uploader, who receives the result notification}
        {title : Notification title, e.g. "Gestión Demanda"}
        {filenamePrefix : Prefix of the result files, e.g. "gestion-demanda-"}';

    protected $description = 'Import a Gestión Demanda / Odontología waitlist file and notify the uploader';

    /** Only these classes may be passed on the command line. */
    private const IMPORT_CLASSES = [
        'DemandManagementWaitlistImport' => DemandManagementWaitlistImport::class,
        'OdontologyWaitlistImport'       => OdontologyWaitlistImport::class,
    ];

    public function handle(): int
    {
        $importClass = self::IMPORT_CLASSES[class_basename($this->argument('importClass'))] ?? null;
        if (! $importClass) {
            $this->error('Unknown import class: ' . $this->argument('importClass'));

            return self::FAILURE;
        }

        $job = new ImportWaitlistJob(
            $importClass,
            $this->argument('filePath'),
            (int) $this->argument('userId'),
            $this->argument('title'),
            $this->argument('filenamePrefix'),
        );

        try {
            $job->handle();
        } catch (\Throwable $e) {
            // Deletes the upload and sends the uploader the error notification.
            $job->failed($e);
            report($e);

            return self::FAILURE;
        }

        $this->info('Import finished.');

        return self::SUCCESS;
    }
}
