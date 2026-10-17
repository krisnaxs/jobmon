<?php

namespace App\Exports;

use App\Models\TeamMember;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Collection;

class WorkOrdersExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithEvents
{
    protected array $filters;
    protected array $photoPositions = [];
    protected int $currentRow = 1;
    protected int $headerRows = 0;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    protected function headerInfo(): array
    {
        $lines = [];
        $lines[] = [
            'text' => 'LAPORAN PEKERJAAN',
            'bold' => true,
            'size' => 16,
            'merge' => true,
            'align' => 'center',
        ];

        $lines[] = [
            'text' => 'Job Monitor di export pada — ' . now()->translatedFormat('d F Y H:i'),
            'bold' => false,
            'size' => 10,
            'merge' => true,
            'align' => 'center',
            'color' => '666666',
        ];

        $lines[] = ['text' => '', 'merge' => true];

        $filterLines = $this->buildFilterLines();
        foreach ($filterLines as $fl) {
            $lines[] = [
                'text' => $fl,
                'bold' => false,
                'size' => 11,
                'merge' => false,
            ];
        }

        $lines[] = ['text' => '', 'merge' => true];

        return $lines;
    }

    protected function buildFilterLines(): array
    {
        $lines = [];
        $f = $this->filters;
        $rangeLabel = $this->rangeLabel($f['range'] ?? 'all', $f['from'] ?? null, $f['to'] ?? null);
        $lines[] = "Periode\t: " . $rangeLabel;
        $division = $f['division'] ?? 'all';
        $divisionLabel = $division === 'all' ? 'Semua Divisi' : strtoupper($division);
        $lines[] = "Divisi\t\t: " . $divisionLabel;
        $status = $f['status'] ?? 'all';
        $statusLabel = $status === 'all' ? 'Semua Status' : $this->statusLabel($status);
        $lines[] = "Status\t\t: " . $statusLabel;
        $techId = $f['technician_id'] ?? 'all';
        if ($techId !== 'all') {
            $tech = \App\Models\User::find($techId);
            $lines[] = "Teknisi\t: " . ($tech?->name ?? "User #{$techId}");
        }
        $team = $f['team'] ?? 'all';
        if ($team !== 'all') {
            $teamMode = $f['team_mode'] ?? 'snapshot';
            $teamLabel = $team === 'none' ? 'Tanpa Tim' : $team;
            $lines[] = "Tim\t\t: " . $teamLabel . " ({$teamMode})";
        }
        $mode = $f['completion_mode'] ?? 'all';
        if ($mode !== 'all') {
            $modeLabel = $mode === 'collective' ? 'Global (Tim)' : 'Individual';
            $lines[] = "Mode\t\t: " . $modeLabel;
        }
        $noPart = $f['no_participation'] ?? 'all';
        if ($noPart !== 'all') {
            $noPartLabel = $noPart === 'yes' ? 'Ada yang Skip' : 'Tanpa Skip';
            $lines[] = "Partisipasi\t: " . $noPartLabel;
        }
        $lines[] = "";
        $lines[] = "Total data ditampilkan: " . ($this->filters['_total_records'] ?? '-');

        return $lines;
    }

    protected function rangeLabel(string $range, $from, $to): string
    {
        return match ($range) {
            'today' => 'Hari Ini (' . now()->translatedFormat('d F Y') . ')',
            'week' => '7 Hari Terakhir',
            'month' => '30 Hari Terakhir',
            'custom' => ($from && $to)
                ? Carbon::parse($from)->translatedFormat('d F Y') . ' — ' . Carbon::parse($to)->translatedFormat('d F Y')
                : 'Custom',
            'all' => 'Semua Waktu',
            default => '-',
        };
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Pending',
            'in_progress' => 'Proses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $status,
        };
    }

    public function collection(): Collection
    {
        $query = WorkOrder::with([
            'creator:id,name',
            'technicians' => function ($q) {
                $q->select('users.id', 'users.name', 'users.username', 'users.division')
                    ->withPivot([
                        'team_name_snapshot',
                        'status',
                        'started_at',
                        'completed_at',
                        'resolution_note',
                        'no_participation',
                        'skipped_at',
                    ]);
            },
            'media' => function ($q) {
                $q->select('id', 'work_order_id', 'uploaded_by', 'type', 'media_type', 'path', 'caption');
            },
        ]);

        $this->applyFilters($query, $this->filters);

        $limit = !empty($this->filters['include_photos']) ? 1000 : 5000;

        $data = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
        $this->filters['_total_records'] = $data->count();

        return $data;
    }

    public function headings(): array
    {
        $base = [
            'Kode',
            'Judul',
            'Deskripsi',
            'Divisi',
            'Kategori',
            'Prioritas',
            'Mode',
            'Status Global',
            'Progress Tim',
            'Teknisi & Status',
            'Tim',
            'Gedung',
            'Lokasi',
            'Dibuat Oleh',
            'Sumber',
            'Tanggal Dibuat',
            'Mulai Global',
            'Selesai Global',
            'Deadline',
            'Catatan Penyelesaian',
        ];

        if (!empty($this->filters['include_photos'])) {
            $base[] = 'Foto Sebelum';
            $base[] = 'Foto Sesudah';
        }

        return $base;
    }

    public function map($wo): array
    {
        $participating = $wo->technicians
            ->filter(fn($t) => ($t->pivot->no_participation ?? false) === false);
        $skipped = $wo->technicians
            ->filter(fn($t) => ($t->pivot->no_participation ?? false) === true);

        $totalTechs = $participating->count();
        $completedTechs = $participating
            ->filter(fn($t) => ($t->pivot->status ?? null) === 'completed')
            ->count();
        $skippedTechs = $skipped->count();

        $progressText = $totalTechs > 0 ? "{$completedTechs}/{$totalTechs}" : '-';
        if ($skippedTechs > 0) {
            $progressText .= " ({$skippedTechs} skip)";
        }

        $techText = $wo->technicians->map(function ($t) {
            $isSkipped = (bool) ($t->pivot->no_participation ?? false);
            $pivotStatus = $t->pivot->status ?? 'pending';

            if ($isSkipped)
                return "{$t->name} ⊘";

            $icon = match ($pivotStatus) {
                'completed' => '✓',
                'in_progress' => '⚙',
                default => '⏸',
            };
            return "{$t->name} {$icon}";
        })->implode(', ');

        if (empty($techText))
            $techText = 'Belum di-assign';

        $teams = $participating
            ->pluck('pivot.team_name_snapshot')
            ->filter()
            ->unique()
            ->values();

        $teamText = $teams->isEmpty() ? '-' : $teams->implode(' + ');

        $modeLabel = match ($wo->completion_mode) {
            'collective' => 'Global (Tim)',
            'individual' => 'Individual',
            default => '-',
        };

        $statusLabel = match ($wo->status) {
            'pending' => 'Pending',
            'in_progress' => 'Proses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $wo->status,
        };

        $sourceLabel = $wo->source === 'technician' ? 'Mandiri (Teknisi)' : 'Dari Admin';

        $priorityLabel = match ($wo->priority) {
            'urgent' => 'Urgent',
            'high' => 'High',
            'normal' => 'Normal',
            'low' => 'Low',
            default => $wo->priority,
        };

        if (!empty($this->filters['include_photos'])) {
            $rowNum = $this->currentRow++;
            $this->photoPositions[$rowNum] = [
                'before' => $wo->media->where('type', 'before')->values(),
                'after' => $wo->media->where('type', 'after')->values(),
            ];
        }

        $row = [
            $wo->code,
            $wo->title,
            $wo->description,
            strtoupper($wo->division),
            $wo->category ?? '-',
            $priorityLabel,
            $modeLabel,
            $statusLabel,
            $progressText,
            $techText,
            $teamText,
            $wo->building ?? '-',
            $wo->location ?? '-',
            $wo->creator?->name ?? '-',
            $sourceLabel,
            $wo->created_at?->format('d/m/Y H:i') ?? '-',
            $wo->started_at?->format('d/m/Y H:i') ?? '-',
            $wo->completed_at?->format('d/m/Y H:i') ?? '-',
            $wo->deadline_at?->format('d/m/Y') ?? '-',
            $wo->resolution_note ?? '-',
        ];

        if (!empty($this->filters['include_photos'])) {
            $row[] = '';
            $row[] = '';
        }

        return $row;
    }

    public function styles(Worksheet $sheet): array
    {
        return [];
    }

    protected function maxColumn(): int
    {
        $base = 20;
        if (!empty($this->filters['include_photos'])) {
            $base += 2;
        }
        return $base;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $maxCol = $this->maxColumn();
                $maxColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($maxCol);
                $headerInfo = $this->headerInfo();
                $this->headerRows = count($headerInfo);

                // ✅ 1. INSERT ROW DULU (geser data ke bawah)
                $sheet->insertNewRowBefore(1, $this->headerRows);

                // ✅ 2. SET HEADER INFO (setelah insert) — hanya SEKALI
                foreach ($headerInfo as $i => $line) {
                    $rowNum = $i + 1;
                    $cell = "A{$rowNum}";

                    if (!empty($line['merge'])) {
                        $sheet->mergeCells("A{$rowNum}:{$maxColLetter}{$rowNum}");
                        $sheet->setCellValue($cell, $line['text']);
                        $sheet->getStyle($cell)->getAlignment()
                            ->setHorizontal($line['align'] ?? Alignment::HORIZONTAL_LEFT)
                            ->setVertical(Alignment::VERTICAL_CENTER);
                    } else {
                        $sheet->setCellValue($cell, $line['text']);
                    }

                    if (!empty($line['bold'])) {
                        $sheet->getStyle($cell)->getFont()->setBold(true);
                    }

                    if (!empty($line['size'])) {
                        $sheet->getStyle($cell)->getFont()->setSize($line['size']);
                    }

                    if (!empty($line['color'])) {
                        $sheet->getStyle($cell)->getFont()->getColor()->setRGB($line['color']);
                    }
                }

                $headingRowNum = $this->headerRows + 1;

                // ✅ 3. STYLE HEADING ROW
                $sheet->getStyle("A{$headingRowNum}:{$maxColLetter}{$headingRowNum}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4F46E5'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // ✅ 4. EMBED FOTO
                if (!empty($this->filters['include_photos'])) {
                    $beforeCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($maxCol - 1);
                    $afterCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($maxCol);

                    foreach ($this->photoPositions as $origRow => $media) {
                        $newRow = $origRow + $this->headerRows;

                        if ($media['before']->count()) {
                            $this->embedImage($sheet, $media['before']->first()->path, "{$beforeCol}{$newRow}");
                        } else {
                            $sheet->setCellValue("{$beforeCol}{$newRow}", '-');
                            $sheet->getStyle("{$beforeCol}{$newRow}")
                                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        }

                        if ($media['after']->count()) {
                            $this->embedImage($sheet, $media['after']->first()->path, "{$afterCol}{$newRow}");
                        } else {
                            $sheet->setCellValue("{$afterCol}{$newRow}", '-');
                            $sheet->getStyle("{$afterCol}{$newRow}")
                                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        }

                        $sheet->getRowDimension($newRow)->setRowHeight(70);
                    }

                    $sheet->getColumnDimension($beforeCol)->setWidth(15);
                    $sheet->getColumnDimension($afterCol)->setWidth(15);
                }

                // ✅ 5. BORDER SEMUA DATA
                $highestRow = $sheet->getHighestRow();

                $sheet->getStyle("A{$headingRowNum}:{$maxColLetter}{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                ]);
            },
        ];
    }

    private function embedImage(Worksheet $sheet, string $storagePath, string $coordinate): void
    {
        $fullPath = storage_path('app/public/' . $storagePath);

        if (!file_exists($fullPath)) {
            $sheet->setCellValue($coordinate, '✗ file hilang');
            return;
        }

        try {
            $drawing = new Drawing();
            $drawing->setName('Foto');
            $drawing->setDescription('Foto bukti');
            $drawing->setPath($fullPath);
            $drawing->setHeight(70);
            $drawing->setCoordinates($coordinate);
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);
            $drawing->setResizeProportional(true);
            $drawing->setWorksheet($sheet);
        } catch (\Exception $e) {
            $sheet->setCellValue($coordinate, '✗ error');
        }
    }

    private function applyFilters($query, array $filters): void
    {
        $range = $filters['range'] ?? 'all';
        $start = null;
        $end = null;

        switch ($range) {
            case 'today':
                $start = now()->startOfDay();
                $end = now()->endOfDay();
                break;
            case 'week':
                $start = now()->subDays(7)->startOfDay();
                $end = now()->endOfDay();
                break;
            case 'month':
                $start = now()->subDays(30)->startOfDay();
                $end = now()->endOfDay();
                break;
            case 'custom':
                if (!empty($filters['from']) && !empty($filters['to'])) {
                    $start = Carbon::parse($filters['from'])->startOfDay();
                    $end = Carbon::parse($filters['to'])->endOfDay();
                }
                break;
        }

        if ($start && $end) {
            $query->where(function ($q) use ($start, $end) {
                $q->where('status', 'pending')
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('status', 'in_progress')
                            ->whereBetween('started_at', [$start, $end]);
                    })
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('status', 'completed')
                            ->whereBetween('completed_at', [$start, $end]);
                    });
            });
        }
        if (($filters['division'] ?? 'all') !== 'all') {
            $query->where('division', $filters['division']);
        }
        if (($filters['status'] ?? 'all') !== 'all') {
            $query->where('status', $filters['status']);
        }
        if (($filters['completion_mode'] ?? 'all') !== 'all') {
            $query->where('completion_mode', $filters['completion_mode']);
        }
        if (($filters['technician_id'] ?? 'all') !== 'all') {
            $techId = $filters['technician_id'];

            $query->where(function ($q) use ($techId) {
                $q->whereHas('technicians', fn($q2) => $q2->where('user_id', $techId))
                    ->orWhere(function ($q2) use ($techId) {
                        $q2->where('created_by', $techId)
                            ->where('source', 'technician');
                    });
            });
        }
        if (($filters['team'] ?? 'all') !== 'all') {
            $teamValue = $filters['team'];
            $teamMode = $filters['team_mode'] ?? 'snapshot';

            if ($teamValue === 'none') {
                if ($teamMode === 'snapshot') {
                    $query->whereDoesntHave(
                        'technicians',
                        fn($q) => $q->whereNotNull('team_name_snapshot')
                    );
                } else {
                    $now = now();
                    $userIdsWithTeam = TeamMember::where('period_month', $now->month)
                        ->where('period_year', $now->year)
                        ->pluck('user_id')
                        ->toArray();

                    $query->where(function ($q) use ($userIdsWithTeam) {
                        $q->whereDoesntHave('technicians')
                            ->orWhereHas(
                                'technicians',
                                fn($q2) => $q2->whereNotIn('user_id', $userIdsWithTeam)
                            );
                    });
                }
            } else {
                if ($teamMode === 'snapshot') {
                    $query->whereHas(
                        'technicians',
                        fn($q) => $q->where('team_name_snapshot', $teamValue)
                    );
                } else {
                    $now = now();
                    $userIds = TeamMember::whereHas(
                        'team',
                        fn($q) => $q->where('name', $teamValue)
                    )
                        ->where('period_month', $now->month)
                        ->where('period_year', $now->year)
                        ->pluck('user_id')
                        ->toArray();

                    $query->whereHas(
                        'technicians',
                        fn($q) => $q->whereIn('user_id', $userIds)
                    );
                }
            }
        }
        if (($filters['no_participation'] ?? 'all') !== 'all') {
            if ($filters['no_participation'] === 'yes') {
                $query->whereHas('technicians', fn($q) => $q->where('no_participation', true));
            } elseif ($filters['no_participation'] === 'no') {
                $query->whereDoesntHave('technicians', fn($q) => $q->where('no_participation', true));
            }
        }

        // ✅ TAMBAH: sembunyikan WO masa depan (khusus teknisi)
        if (!empty($filters['hide_future'])) {
            $query->whereDate('created_at', '<=', today());
        }
    }
}
