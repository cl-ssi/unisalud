<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SigteDuplicatesSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    /**
     * @param  array<string>  $headers
     * @param  array<int, array<string, string>>  $rows
     */
    public function __construct(
        private readonly array $headers,
        private readonly array $rows,
        private readonly string $title,
    ) {}

    public function array(): array
    {
        return array_map(
            fn (array $row) => array_map(fn (string $header) => $row[$header] ?? '', $this->headers),
            $this->rows
        );
    }

    public function headings(): array
    {
        return $this->headers;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
