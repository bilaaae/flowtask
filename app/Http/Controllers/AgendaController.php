<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * =========================================================================================
 * CONTROLLER: AgendaController (Pengendali Halaman Daftar Agenda / List View)
 * =========================================================================================
 * Controller ini bertugas mengambil seluruh data agenda/tugas pengguna dari database MySQL,
 * memformatnya untuk tampilan baris ke bawah (List View), mendukung filter dan sorting,
 * serta menyiapkan data batas waktu (deadline) untuk sistem pengingat tugas mendesak.
 * 
 * Penguji Ujian:
 * "Apa kegunaan fungsi index() di AgendaController ini?"
 * Jawab: Mengambil seluruh tugas milik user yang sedang login dari tabel 'tasks' MySQL,
 * mengurutkannya secara default berdasarkan tanggal batas waktu (due_date) terdekat,
 * memetakan data persentase progres subtugas, dan mengirimkannya ke view 'agenda.blade.php'.
 * =========================================================================================
 */
class AgendaController extends Controller
{
    /**
     * Menampilkan halaman Daftar Agenda dengan tampilan baris ke bawah (List View).
     *
     * Data yang diambil dari MySQL:
     * - id, title, description, category, priority, status, due_date
     * - subtasks, subtasks_total, subtasks_completed, order
     *
     * Dikirim ke:
     * - File Blade: resources/views/agenda.blade.php
     *
     * @return View
     */
    public function index(): View
    {
        $userId = Auth::id();

        // Inisialisasi otomatis data contoh jika akun user belum memiliki tugas
        if (Task::where('user_id', $userId)->count() === 0) {
            app(TaskController::class)->seedStarterTasks($userId);
        }

        // 1. Ambil seluruh tugas user dari database MySQL terurut berdasarkan tanggal deadline terdekat
        $tasks = Task::where('user_id', $userId)
            ->orderBy('due_date', 'asc')
            ->orderBy('priority', 'asc')
            ->get();

        // 2. Format koleksi data agar kompatibel penuh dengan reaktivitas Alpine.js di agenda.blade.php
        $agendas = $tasks->map(function ($task) {
            // Tentukan warna aksen kartu berdasarkan prioritas/kategori
            $color = match ($task->priority) {
                'Tinggi' => 'rose',
                'Sedang' => 'amber',
                'Rendah' => 'emerald',
                default => 'indigo',
            };

            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description ?? '',
                'category' => $task->category,
                'status' => $task->status,
                'priority' => $task->priority,
                'progress' => $task->progress_percentage,
                'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                'formatted_due_date' => $task->due_date ? $task->due_date->format('d M Y') : 'Tanpa batas waktu',
                'tasks_count' => $task->subtasks_total,
                'completed_tasks' => $task->subtasks_completed,
                'subtasks' => $task->subtasks ?? [],
                'color' => $color,
            ];
        });

        // 3. Kembalikan view agenda dengan data yang siap diolah secara reaktif oleh Alpine.js
        return view('agenda', compact('agendas', 'tasks'));
    }
}
