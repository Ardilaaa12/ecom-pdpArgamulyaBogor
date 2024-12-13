<?php

namespace App\Exports;

use App\Models\Shipping;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ShippingReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles 
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
            ['Laporan Pengiriman Domba'],
            [],
            ['No.Ref', 'No.Pengiriman', 'Tanggal Pengiriman', 'Nama Penerima', 'Alamat Pengiriman', 'status'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:F1');

        $sheet->getStyle('A3:F3')->getAlignment()->setHorizontal('center');

        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            3 => ['font' => ['bold' => true]],
        ];
    }
}
