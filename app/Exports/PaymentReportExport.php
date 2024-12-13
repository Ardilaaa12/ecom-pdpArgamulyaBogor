<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles 
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
            ['Laporan Pembayaran Customer'],
            [],
            ['No.Ref', 'No.Bayar', 'Tanggal Pembayaran', 'Metode Pembayaran', 'Total Bayar', 'Atas Nama', 'status'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:F1');

        $sheet->getStyle('A3:G3')->getAlignment()->setHorizontal('center');

        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            3 => ['font' => ['bold' => true]],
        ];
    }
}
