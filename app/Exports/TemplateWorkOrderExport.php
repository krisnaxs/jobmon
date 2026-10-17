<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TemplateWorkOrderExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'created_at',
            'judul_tugas',
            'deskripsi',
            'divisi_pekerjaan',
            'kategori',
            'gedung',
            'lokasi_ruangan',
            'prioritas',
            'deadline',
            'mode_penyelesaian',
            'tugaskan_ke',
        ];
    }

    public function array(): array
    {
        return [
            [
                '01/12/2026',
                'Ganti kamera lantai 1',
                'Kamera mati total',
                'cctv',
                'Kamera',
                'Gedung A',
                'Lantai 1',
                'high',
                '05/12/2026',
                'individual',
                'teknisi.a, teknisi.b',
            ],
            [
                '02/12/2026',
                'Cek jaringan lantai 2',
                '',
                'helpdesk',
                'Jaringan',
                'Gedung B',
                'Lantai 2',
                'normal',
                '',
                'collective',
                '',
            ],
            [
                '03/12/2026',
                'Install printer ruang meeting',
                '',
                'helpdesk',
                'Printer',
                'Gedung C',
                'Lantai 3',
                'low',
                '10/12/2026',
                'individual',
                'teknisi.c',
            ],
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        // Style header (baris 1)
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
        ]);

        // Tinggi baris header
        $sheet->getRowDimension(1)->setRowHeight(24);

        // Format kolom created_at (A) & deadline (I) sebagai TEXT
        // supaya user input dd/mm/yyyy tidak di-convert jadi Date/angka oleh Excel
        $sheet->getStyle('A2:A1000')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT);
        $sheet->getStyle('I2:I1000')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT);

        // Auto width semua kolom
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return null;
    }
}
