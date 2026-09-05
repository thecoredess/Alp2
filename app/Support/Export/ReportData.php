<?php

namespace App\Support\Export;

/**
 * Struktur data laporan yang neutral-format. Digunakan oleh XlsxWriter & PdfWriter
 * supaya nilai (terutamanya wang sebagai string DECIMAL kanonik) tidak melalui float.
 *
 * Column: ['label' => string, 'type' => 'text'|'money'|'number'|'date', 'align' => 'left'|'right'|'center', 'width' => int]
 * Row: senarai nilai skalar mengikut susunan column. Nilai wang mesti string kanonik "12345.67".
 */
class ReportData
{
    /**
     * @param  array<int, array{label: string, type?: string, align?: string, width?: int}>  $columns
     * @param  array<int, array<int, string|int|null>>  $rows
     * @param  array<string, string>  $meta
     */
    public function __construct(
        public readonly string $title,
        public readonly array $columns,
        public readonly array $rows,
        public readonly array $meta = [],
    ) {}

    public function columnType(int $i): string
    {
        return $this->columns[$i]['type'] ?? 'text';
    }

    public function columnAlign(int $i): string
    {
        return $this->columns[$i]['align'] ?? ($this->columnType($i) === 'money' || $this->columnType($i) === 'number' ? 'right' : 'left');
    }
}
