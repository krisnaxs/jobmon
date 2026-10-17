<?php

namespace App\Imports;

use App\Models\User;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Row;

class WorkOrderImport implements OnEachRow, WithHeadingRow, SkipsEmptyRows
{
    protected array $errors = [];
    protected int $success = 0;

    public function onRow(Row $row): void
    {
        $data = $row->toArray();
        $rowIndex = $row->getIndex();

        // 1. Validasi kolom wajib
        $required = [
            'created_at' => 'Created At',
            'judul_tugas' => 'Judul Tugas',
            'divisi_pekerjaan' => 'Divisi Pekerjaan',
            'prioritas' => 'Prioritas',
            'mode_penyelesaian' => 'Mode Penyelesaian',
        ];

        foreach ($required as $key => $label) {
            if (empty($data[$key])) {
                $this->errors[] = "Baris {$rowIndex}: kolom '{$label}' wajib diisi.";
                return;
            }
        }

        // 2. Parsing created_at
        try {
            $rawDate = trim((string) $data['created_at']);

            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $rawDate)) {
                $createdAt = Carbon::createFromFormat('d/m/Y', $rawDate)->startOfDay();
            } elseif (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $rawDate)) {
                $createdAt = Carbon::createFromFormat('Y-m-d', $rawDate)->startOfDay();
            } else {
                $createdAt = Carbon::parse($rawDate)->startOfDay();
            }
        } catch (\Exception $e) {
            $this->errors[] = "Baris {$rowIndex}: format Created At tidak valid ({$data['created_at']}).";
            return;
        }

        // 3. Validasi divisi
        $division = strtolower(trim($data['divisi_pekerjaan']));
        if (!in_array($division, ['cctv', 'helpdesk'])) {
            $this->errors[] = "Baris {$rowIndex}: Divisi harus 'cctv' atau 'helpdesk'.";
            return;
        }

        // 4. Validasi prioritas
        $priority = strtolower(trim($data['prioritas']));
        if (!in_array($priority, ['urgent', 'high', 'normal', 'low'])) {
            $this->errors[] = "Baris {$rowIndex}: Prioritas harus urgent/high/normal/low.";
            return;
        }

        // 5. Validasi mode penyelesaian
        $mode = strtolower(trim($data['mode_penyelesaian']));
        if (!in_array($mode, ['collective', 'individual'])) {
            $this->errors[] = "Baris {$rowIndex}: Mode harus 'collective' atau 'individual'.";
            return;
        }

        // 6. Parsing deadline (opsional)
        $deadlineAt = null;
        if (!empty($data['deadline'])) {
            try {
                $rawDeadline = trim((string) $data['deadline']);

                if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $rawDeadline)) {
                    $deadlineAt = Carbon::createFromFormat('d/m/Y', $rawDeadline)->startOfDay();
                } elseif (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $rawDeadline)) {
                    $deadlineAt = Carbon::createFromFormat('Y-m-d', $rawDeadline)->startOfDay();
                } else {
                    $deadlineAt = Carbon::parse($rawDeadline)->startOfDay();
                }
            } catch (\Exception $e) {
                $this->errors[] = "Baris {$rowIndex}: format Deadline tidak valid ({$data['deadline']}).";
                return;
            }
        }

        // 7. Parsing teknisi (opsional)
        $technicians = collect();
        $rawTechnicians = trim($data['tugaskan_ke'] ?? '');

        if ($rawTechnicians !== '') {
            $usernames = array_filter(
                array_map('trim', explode(',', $rawTechnicians)),
                fn($u) => $u !== ''
            );

            if (count($usernames) === 0) {
                $this->errors[] = "Baris {$rowIndex}: kolom 'Tugaskan Ke' kosong setelah split.";
                return;
            }

            $technicians = User::whereIn('username', $usernames)
                ->where('role', 'technician')
                ->where('is_active', true)
                ->get();

            if ($technicians->count() !== count($usernames)) {
                $missing = array_diff($usernames, $technicians->pluck('username')->toArray());
                $this->errors[] = "Baris {$rowIndex}: teknisi tidak ditemukan → " . implode(', ', $missing);
                return;
            }

            $wrongDivision = $technicians->filter(fn($t) => $t->division !== $division);
            if ($wrongDivision->count() > 0) {
                $names = $wrongDivision
                    ->map(fn($t) => "{$t->username} ({$t->division})")
                    ->implode(', ');
                $this->errors[] = "Baris {$rowIndex}: teknisi beda divisi ({$division}) → {$names}";
                return;
            }
        }

        // 8. Buat Work Order
        $wo = WorkOrder::create([
            'title' => trim($data['judul_tugas']),
            'description' => $data['deskripsi'] ?? null,
            'division' => $division,
            'category' => $data['kategori'] ?? null,
            'building' => $data['gedung'] ?? null,
            'location' => $data['lokasi_ruangan'] ?? null,
            'priority' => $priority,
            'deadline_at' => $deadlineAt,
            'completion_mode' => $mode,
            'status' => 'pending',
            'source' => 'admin',
            'created_by' => auth()->id(),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        // 9. Assign teknisi
        if ($technicians->count() > 0) {
            $wo->assignMany($technicians->pluck('id')->toArray(), auth()->id());
        }

        $this->success++;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getSuccessCount(): int
    {
        return $this->success;
    }
}
