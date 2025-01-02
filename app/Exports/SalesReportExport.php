<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct($data)
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
            ['Laporan Penjualan PDP Argamulya Bogor'], // Judul
            [], // Baris kosong
            ['No. Ref', 'Tanggal Order', 'Nama', 'Total', 'Status Pembayaran'], // Header tabel
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center'); // Rata tengah judul
        $sheet->mergeCells('A1:E1'); // Gabungkan sel untuk judul
        $sheet->getStyle('A3:E3')->getAlignment()->setHorizontal('center'); // Rata tengah header tabel
        $sheet->getStyle('D4:D' . ($sheet->getHighestRow()))->getNumberFormat()->setFormatCode('#,##0'); // Format angka

        return [
            1 => ['font' => ['bold' => true, 'size' => 14]], // Judul
            3 => ['font' => ['bold' => true]], // Header tabel
        ];
    }
}
