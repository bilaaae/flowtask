<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * =========================================================================================
 * MODEL ELOQUENT: Task (Tugas)
 * =========================================================================================
 * Model ini bertindak sebagai jembatan Object-Relational Mapping (ORM) antara tabel 'tasks' 
 * di database MySQL dengan logika aplikasi PHP di Laravel.
 * 
 * Penguji Ujian: 
 * "Apa fungsi file Model ini?"
 * Jawab: Model Task merepresentasikan entitas data tugas dan subtugas, mengatur kolom yang
 * boleh diisi (mass assignment), konversi tipe data otomatis (casting), relasi ke tabel users,
 * serta perhitungan persentase kemajuan (progress bar) dan status pengingat deadline.
 * =========================================================================================
 */
class Task extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database MySQL yang dihubungkan dengan model ini.
     * Secara default Laravel akan menggunakan bentuk jamak 'tasks'.
     */
    protected $table = 'tasks';

    /**
     * Kolom-kolom yang diizinkan untuk diisi secara massal (Mass Assignment Protection).
     * Mencegah eksploitasi keamanan di mana pengguna luar menyisipkan kolom berbahaya.
     */
    protected $fillable = [
        'user_id',            // ID pengguna pemilik tugas (Foreign Key ke tabel users)
        'title',              // Judul ringkas tugas (misal: "Desain Prototipe")
        'description',        // Deskripsi detail ruang lingkup tugas
        'category',           // Kategori bidang kerja (misal: "Desain UI/UX", "Backend")
        'priority',           // Tingkat prioritas tugas: "Tinggi", "Sedang", atau "Rendah"
        'status',             // Kolom Kanban: "Belum Dikerjakan", "Sedang Dikerjakan", "Selesai"
        'due_date',           // Tanggal batas waktu (deadline) tugas
        'subtasks_total',     // Total jumlah butir subtugas (integer)
        'subtasks_completed', // Jumlah subtugas yang sudah dicentang selesai (integer)
        'subtasks',           // Data array/JSON daftar subtugas [{title: "...", completed: true/false}]
        'order',              // Urutan kartu di dalam kolom Kanban (integer)
    ];

    /**
     * Konversi tipe data otomatis saat data diambil dari atau disimpan ke MySQL (Attribute Casting).
     * - 'due_date' diubah otomatis menjadi objek Carbon / Date
     * - 'subtasks' diubah otomatis dari format JSON string di MySQL menjadi Array di PHP
     */
    protected $casts = [
        'due_date' => 'date',
        'subtasks_total' => 'integer',
        'subtasks_completed' => 'integer',
        'subtasks' => 'array',
        'order' => 'integer',
    ];

    /**
     * Properti accessor bawaan yang otomatis disertakan saat model diubah ke format Array/JSON.
     */
    protected $appends = [
        'progress_percentage',
        'is_due_today',
        'is_due_tomorrow',
        'is_overdue',
        'days_remaining_info',
    ];

    /**
     * Relasi Eloquent: Task dimiliki oleh satu User (One-to-Many Inverse / BelongsTo).
     * Menghubungkan kolom tasks.user_id dengan users.id.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ACCESSOR: Menghitung persentase kemajuan (Progress Bar) secara dinamis.
     * Logika:
     * - Jika ada subtugas (total > 0), kemajuan dihitung dari (subtasks_completed / subtasks_total) * 100.
     * - Jika tidak ada subtugas, persentase adalah 100% jika status "Selesai", atau 0% jika belum.
     * 
     * Nilai ini dapat dipanggil di Controller/Blade dengan: $task->progress_percentage
     */
    public function getProgressPercentageAttribute(): int
    {
        if ($this->subtasks_total > 0) {
            return (int) round(($this->subtasks_completed / $this->subtasks_total) * 100);
        }

        return $this->status === 'Selesai' ? 100 : 0;
    }

    /**
     * ACCESSOR: Memeriksa apakah batas waktu tugas jatuh pada HARI INI.
     * Digunakan untuk sistem pengingat deadline mendesak (badge merah menyala).
     */
    public function getIsDueTodayAttribute(): bool
    {
        if (!$this->due_date) return false;
        return $this->due_date->isToday();
    }

    /**
     * ACCESSOR: Memeriksa apakah batas waktu tugas jatuh pada BESOK.
     * Digunakan untuk sistem pengingat deadline peringatan (badge oranye menyala).
     */
    public function getIsDueTomorrowAttribute(): bool
    {
        if (!$this->due_date) return false;
        return $this->due_date->isTomorrow();
    }

    /**
     * ACCESSOR: Memeriksa apakah tugas sudah melewati batas waktu (Terlambat).
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->due_date || $this->status === 'Selesai') return false;
        return $this->due_date->isPast() && !$this->due_date->isToday();
    }

    /**
     * ACCESSOR: Menghasilkan informasi teks dan status visual batas waktu.
     * Menghasilkan teks: "Hari ini", "Besok", "X hari lagi", atau "Lewat X hari".
     */
    public function getDaysRemainingInfoAttribute(): array
    {
        if (!$this->due_date) {
            return [
                'text' => 'Tanpa batas waktu',
                'status' => 'normal',
                'badge_class' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400',
            ];
        }

        $today = Carbon::today();
        $due = Carbon::parse($this->due_date)->startOfDay();

        if ($due->isToday()) {
            return [
                'text' => 'Hari ini',
                'status' => 'today',
                'badge_class' => 'bg-red-500 text-white font-extrabold shadow-md animate-pulse',
            ];
        } elseif ($due->isTomorrow()) {
            return [
                'text' => 'Besok',
                'status' => 'tomorrow',
                'badge_class' => 'bg-amber-500 text-white font-extrabold shadow-sm',
            ];
        } elseif ($due->isPast()) {
            $days = (int) $due->diffInDays($today);
            return [
                'text' => "Lewat {$days} hari",
                'status' => 'overdue',
                'badge_class' => 'bg-rose-700 text-white font-extrabold shadow-sm',
            ];
        } else {
            $days = (int) $today->diffInDays($due);
            return [
                'text' => "{$days} hari lagi",
                'status' => 'upcoming',
                'badge_class' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium',
            ];
        }
    }
}
