<?php

namespace App\Jobs;

use App\Services\SigteImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportSigteLeCneJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public int $tries = 1;

    public function __construct(public string $filePath)
    {
    }

    public function handle(SigteImportService $importer): void
    {
        try {
            $importer->processLeCne($this->filePath);
        } finally {
            Storage::disk('local')->delete($this->filePath);
        }
    }

    public function failed(\Throwable $e): void
    {
        // Leave the last successful import's meta in place — the page shows
        // it as the current state of the LE CNE database — and just record
        // the failure for follow-up.
        Log::error('LE CNE import failed', ['file' => $this->filePath, 'exception' => $e]);

        Storage::disk('local')->delete($this->filePath);
    }
}
