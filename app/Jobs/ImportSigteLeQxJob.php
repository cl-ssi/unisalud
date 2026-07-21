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

class ImportSigteLeQxJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public int $tries = 1;

    public function __construct(public string $filePath, public ?int $uploadedBy)
    {
    }

    public function handle(SigteImportService $importer): void
    {
        try {
            $importer->processLeQx($this->filePath, $this->uploadedBy);
        } finally {
            Storage::disk('local')->delete($this->filePath);
        }
    }

    public function failed(\Throwable $e): void
    {
        // processLeQx wraps the delete-and-replace in a DB transaction, so a
        // failure rolls back cleanly and the previous snapshot stays intact.
        Log::error('LE Quirúrgica import failed', ['file' => $this->filePath, 'exception' => $e]);

        Storage::disk('local')->delete($this->filePath);
    }
}
