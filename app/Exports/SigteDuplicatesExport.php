<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Two-sheet report of RUNs flagged during a SIGTE LE CNE / LE Quirúrgica
 * import: rows whose RUN repeats within the uploaded file, and rows whose
 * RUN already existed before this upload. Built with the same $headers the
 * source file used, since LE CNE and LE Quirúrgica exports don't share a
 * column layout.
 */
class SigteDuplicatesExport implements WithMultipleSheets
{
    /**
     * @param  array<string>  $headers
     * @param  array<int, array<string, string>>  $inFileDuplicates
     * @param  array<int, array<string, string>>  $preExisting
     */
    public function __construct(
        private readonly array $headers,
        private readonly array $inFileDuplicates,
        private readonly array $preExisting,
    ) {}

    public function sheets(): array
    {
        return [
            new SigteDuplicatesSheet($this->headers, $this->inFileDuplicates, 'Duplicados en archivo'),
            new SigteDuplicatesSheet($this->headers, $this->preExisting, 'Ya existian antes'),
        ];
    }
}
