<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SheepReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles 
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            ['Laporan Stok Domba'],
            [],
            ['ID Domba', 'Nama Domba', 'Kategori', 'Umur', 'Berat', 'Harga Satuan', 'Sisa Stock', 'Status'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:H1');

        $sheet->getStyle('A3:G3')->getAlignment()->setHorizontal('center');

        $sheet->getStyle('D4:D' . ($sheet->getHighestRow()))->getNumberFormat()->setFormatCode('#');

        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            3 => ['font' => ['bold' => true]],
        ];
    }
}
