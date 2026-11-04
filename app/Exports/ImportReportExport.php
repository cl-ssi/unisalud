<?php

namespace App\Exports;

use App\Services\SigteImportService;
use Closure;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;

/**
 * Result files of a waitlist import, linked from the import notification's
 * buttons: the rejected rows and the log of updated data.
 *
 * Files are kept per user under import-reports/{user id} on the uploads disk
 * (workers and web instances share it) for a day, and served by the signed
 * "import-reports.download" route.
 */
class ImportReportExport
{
    public const DIRECTORY = 'import-reports';

    /**
     * The rows the import left out, with the uploaded file's own columns so
     * they can be corrected and uploaded again, plus why each was rejected.
     *
     * @param string[] $headings the uploaded file's heading row
     * @param array<int, array{row: int, reasons: string[], values: array}> $rows
     * @return string download URL
     */
    public static function rejected(array $headings, array $rows, string $filenamePrefix): string
    {
        return self::store($filenamePrefix . 'filas-rechazadas-', 'Rechazados', function (Writer $writer) use ($headings, $rows) {
            // Extra columns go last: the import ignores headings it doesn't know,
            // so the corrected file can be uploaded as is.
            $writer->addRow(Row::fromValues([...$headings, 'FILA ORIGINAL', 'MOTIVO RECHAZO']));
            foreach ($rows as $row) {
                $values = array_pad(array_slice($row['values'], 0, count($headings)), count($headings), null);
                $values = array_map(fn ($value) => $value instanceof \DateTimeInterface ? $value->format('d-m-Y') : $value, $values);
                $writer->addRow(Row::fromValues([...$values, $row['row'], implode("\n", $row['reasons'])]));
            }
        });
    }

    /**
     * One line per changed field of each updated row.
     *
     * @param array<int, array{row: int, run: ?string, created: bool, changes: string[]}> $rows
     *        changes as "Label: old → new"
     * @return string download URL
     */
    public static function changes(array $rows, string $filenamePrefix): string
    {
        return self::store($filenamePrefix . 'cambios-', 'Cambios', function (Writer $writer) use ($rows) {
            $writer->addRow(Row::fromValues(['FILA', 'RUN', 'CASO', 'CAMPO', 'ANTES', 'DESPUÉS']));
            foreach ($rows as $row) {
                foreach ($row['changes'] as $change) {
                    preg_match('/^(.*?): (.*) → (.*)$/su', $change, $m);
                    $writer->addRow(Row::fromValues([
                        $row['row'],
                        $row['run'],
                        $row['created'] ? 'Nuevo' : 'Existente',
                        $m[1] ?? $change,
                        $m[2] ?? null,
                        $m[3] ?? null,
                    ]));
                }
            }
        });
    }

    private static function store(string $filenamePrefix, string $sheetName, Closure $write): string
    {
        $disk = Storage::disk(SigteImportService::uploadsDisk());
        $userDirectory = self::DIRECTORY . '/' . auth()->id();

        foreach ($disk->allFiles($userDirectory) as $old) {
            if ($disk->lastModified($old) < now()->subDay()->getTimestamp()) {
                $disk->delete($old);
            }
        }

        $token = (string) Str::uuid();
        $filename = $filenamePrefix . date('Y-m-d_H-i-s') . '.xlsx';

        $options = new Options();
        $options->SHOULD_USE_INLINE_STRINGS = false;
        $localPath = tempnam(sys_get_temp_dir(), 'import-report-');
        $writer = new Writer($options);
        $writer->openToFile($localPath);
        $writer->getCurrentSheet()->setName($sheetName);
        $write($writer);
        $writer->close();

        $stream = fopen($localPath, 'r');
        $disk->writeStream("$userDirectory/$token/$filename", $stream, ['visibility' => 'private']);
        if (is_resource($stream)) {
            fclose($stream);
        }
        unlink($localPath);

        // Relative: the worker's APP_URL may not be the host the user browses.
        return URL::temporarySignedRoute('import-reports.download', now()->addDay(), [
            'token'    => $token,
            'filename' => $filename,
        ], absolute: false);
    }
}
