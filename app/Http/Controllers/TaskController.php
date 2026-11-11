<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * =========================================================================================
 * CONTROLLER: TaskController (Pengendali Alur Logika Tugas & Kanban)
 * =========================================================================================
 * Controller ini mengatur seluruh siklus hidup data tugas (CRUD: Create, Read, Update, Delete),
 * interaksi kartu Kanban (Drag-and-Drop & tombol pindah status), pengelolaan checklist subtugas,
 * hingga perhitungan persentase kemajuan (progress bar).
 * 
 * Penguji Ujian: 
 * "Apa peran Controller dalam arsitektur MVC Laravel?"
 * Jawab: Controller bertindak sebagai pengendali lalu lintas (traffic controller). Ia menerima
 * HTTP request dari pengguna, memvalidasi input, berinteraksi dengan Model Eloquent untuk
 * mengambil/menyimpan data ke database MySQL, dan mengembalikan tampilan View (Blade) atau
 * respons data (JSON/Redirect).
 * =========================================================================================
 */
class TaskController extends Controller
{
    /**
     * READ: Menampilkan Halaman Papan Kanban (Dashboard).
     * 
     * Alur Kerja:
     * 1. Mengambil data tugas dari tabel 'tasks' di MySQL yang HANYA milik pengguna yang sedang login (Auth::id()).
     * 2. Mengurutkan data berdasarkan kolom 'order' lalu 'created_at' terbaru.
     * 3. Mengelompokkan tugas ke dalam 3 koleksi kolom Kanban:
     *    - 'Belum Dikerjakan'
     *    - 'Sedang Dikerjakan'
     *    - 'Selesai'
     * 4. Menghitung ringkasan statistik (total tugas, tugas selesai, dan persentase produktivitas hari ini).
     * 5. Mengirimkan data tersebut ke file tampilan: resources/views/dashboard.blade.php.
     *
     * @return View
     */
    public function index(): View
    {
        $userId = Auth::id();

        // Inisialisasi otomatis data contoh jika akun user belum memiliki tugas
        if (Task::where('user_id', $userId)->count() === 0) {
            $this->seedStarterTasks($userId);
        }

        // 1. Ambil seluruh tugas user dari database MySQL
        $allTasks = Task::where('user_id', $userId)
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Pisahkan tugas ke dalam 3 kolom Kanban sesuai status
        $tasksTodo = $allTasks->where('status', 'Belum Dikerjakan')->values();
        $tasksInProgress = $allTasks->where('status', 'Sedang Dikerjakan')->values();
        $tasksDone = $allTasks->where('status', 'Selesai')->values();

        // 3. Hitung statistik untuk kartu ringkasan di dashboard
        $totalTasks = $allTasks->count();
        $completedTasks = $tasksDone->count();
        $inProgressTasks = $tasksInProgress->count();
        $todoTasks = $tasksTodo->count();

        // 4. Hitung persentase target produktivitas keseluruhan
        $overallProgress = $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 0;

        // 5. Kembalikan view dashboard dengan membawa seluruh variabel data
        return view('dashboard', compact(
            'allTasks',
            'tasksTodo',
            'tasksInProgress',
            'tasksDone',
            'totalTasks',
            'completedTasks',
            'inProgressTasks',
            'todoTasks',
            'overallProgress'
        ));
    }

    /**
     * CREATE: Menyimpan Tugas Baru ke Database MySQL (Store CRUD).
     * 
     * Alur Kerja:
     * 1. Memvalidasi data input form tambah tugas (judul wajib diisi, validasi enum status & prioritas).
     * 2. Memproses daftar subtugas jika ada, menghitung total subtugas dan yang telah selesai.
     * 3. Membuat baris data baru di tabel MySQL 'tasks' dengan mengaitkan user_id yang sedang login.
     * 4. Mengembalikan pengguna ke halaman sebelumnya dengan flash message notifikasi berhasil.
     *
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        // 1. Validasi input yang dikirim dari form
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'priority' => 'required|in:Tinggi,Sedang,Rendah',
            'status' => 'required|in:Belum Dikerjakan,Sedang Dikerjakan,Selesai',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
            'subtasks' => 'nullable|array',
        ]);

        // 2. Olah data subtugas (jika pengguna menyertakan butir subtugas)
        $subtasksData = [];
        $completedCount = 0;

        if ($request->has('subtasks') && is_array($request->subtasks)) {
            foreach ($request->subtasks as $st) {
                // Pastikan judul subtugas tidak kosong
                if (!empty($st['title'])) {
                    $isDone = !empty($st['completed']) && ($st['completed'] === '1' || $st['completed'] === true || $st['completed'] === 'true' || $st['completed'] === 'on');
                    if ($isDone) {
                        $completedCount++;
                    }
                    $subtasksData[] = [
                        'title' => trim($st['title']),
                        'completed' => $isDone,
                    ];
                }
            }
        }

        // 3. Simpan data tugas baru ke database MySQL melalui Model Task
        $task = Task::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'] ?? 'Pekerjaan',
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'due_date' => $validated['due_date'] ?? null,
            'subtasks' => $subtasksData,
            'subtasks_total' => count($subtasksData),
            'subtasks_completed' => $completedCount,
            'order' => 0,
        ]);

        // 4. Jika request berupa AJAX, kirim respons JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tugas baru berhasil disimpan!',
                'task' => $task,
            ], 201);
        }

        // 5. Kembalikan ke halaman sebelumnya dengan pesan notifikasi sukses
        return redirect()->back()->with('success', 'Tugas "' . $task->title . '" berhasil ditambahkan ke papan kerja!');
    }

    /**
     * UPDATE: Memperbarui Data Tugas & Checklist Subtugas (Update CRUD).
     * 
     * Alur Kerja:
     * 1. Memastikan pengguna hanya dapat mengedit tugas miliknya sendiri (Otorisasi kepemilikan).
     * 2. Memvalidasi data masukan dari form Modal Edit.
     * 3. Memproses array subtugas: mencatat butir subtugas yang dicentang (completed = true/false).
     * 4. Menghitung ulang 'subtasks_total' dan 'subtasks_completed' agar progress bar otomatis terbarui.
     * 5. Menyimpan perubahan ke tabel MySQL 'tasks'.
     * 6. Mengembalikan pengguna ke halaman asal dengan pesan flash session sukses.
     *
     * @param Request $request
     * @param Task $task
     * @return RedirectResponse|JsonResponse
     */
    public function update(Request $request, Task $task): RedirectResponse|JsonResponse
    {
        // 1. Otorisasi: Pastikan tugas milik user yang sedang login
        if ($task->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah tugas ini.');
        }

        // 2. Validasi input dari form edit
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'priority' => 'required|in:Tinggi,Sedang,Rendah',
            'status' => 'required|in:Belum Dikerjakan,Sedang Dikerjakan,Selesai',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
            'subtasks' => 'nullable|array',
        ]);

        // 3. Olah daftar subtugas dan hitung status centang yang selesai
        $subtasksData = [];
        $completedCount = 0;

        if ($request->has('subtasks') && is_array($request->subtasks)) {
            foreach ($request->subtasks as $st) {
                if (!empty($st['title'])) {
                    // Cek apakah subtugas ini dicentang di form modal
                    $isDone = !empty($st['completed']) && ($st['completed'] === '1' || $st['completed'] === true || $st['completed'] === 'true' || $st['completed'] === 'on');
                    if ($isDone) {
                        $completedCount++;
                    }
                    $subtasksData[] = [
                        'title' => trim($st['title']),
                        'completed' => $isDone,
                    ];
                }
            }
        }

        // 4. Perbarui data tugas ke MySQL
        $task->title = $validated['title'];
        $task->category = $validated['category'] ?? $task->category;
        $task->priority = $validated['priority'];
        $task->status = $validated['status'];
        $task->due_date = $validated['due_date'] ?? null;
        $task->description = $validated['description'] ?? null;
        $task->subtasks = $subtasksData;
        $task->subtasks_total = count($subtasksData);
        $task->subtasks_completed = $completedCount;
        $task->save();

        // 5. Respons untuk AJAX
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil diperbarui!',
                'task' => $task,
                'progress' => $task->progress_percentage,
            ]);
        }

        // 6. Redirect kembali ke halaman asal (Kanban atau Agenda) dengan notifikasi sukses
        return redirect()->back()->with('success', 'Perubahan pada tugas "' . $task->title . '" berhasil disimpan!');
    }

    /**
     * UPDATE STATUS KHUSUS: Mengubah Status Kolom Kanban (Drag-and-Drop & Tombol Cepat).
     * 
     * Alur Kerja:
     * 1. Dipanggil saat pengguna menyeret kartu ke kolom lain (Drag & Drop) atau memilih status dari dropdown.
     * 2. Menerima request PATCH dengan parameter 'status' baru:
     *    ('Belum Dikerjakan', 'Sedang Dikerjakan', atau 'Selesai').
     * 3. Menyimpan status baru langsung ke database MySQL.
     * 4. Mengembalikan respons JSON agar antarmuka kartu dapat langsung berpindah secara real-time.
     *
     * @param Request $request
     * @param Task $task
     * @return JsonResponse
     */
    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        // 1. Otorisasi kepemilikan tugas
        if ($task->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Tidak diizinkan.'], 403);
        }

        // 2. Validasi status baru
        $request->validate([
            'status' => 'required|in:Belum Dikerjakan,Sedang Dikerjakan,Selesai',
            'order' => 'nullable|integer',
        ]);

        // 3. Simpan status baru ke MySQL
        $task->status = $request->status;
        if ($request->has('order')) {
            $task->order = (int) $request->order;
        }
        $task->save();

        // 4. Kirim respons JSON sukses ke JavaScript / Alpine.js
        return response()->json([
            'success' => true,
            'message' => 'Status tugas berhasil diperbarui ke: ' . $task->status,
            'task' => $task,
        ]);
    }

    /**
     * DELETE: Menghapus Tugas dari Database MySQL (Destroy CRUD).
     * 
     * Alur Kerja:
     * 1. Memverifikasi kepemilikan tugas milik pengguna aktif.
     * 2. Menghapus rekaman baris tugas dari tabel MySQL 'tasks'.
     * 3. Mengembalikan pengguna ke halaman semula dengan pesan notifikasi sukses.
     *
     * @param Request $request
     * @param Task $task
     * @return RedirectResponse|JsonResponse
     */
    public function destroy(Request $request, Task $task): RedirectResponse|JsonResponse
    {
        // 1. Otorisasi kepemilikan
        if ($task->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus tugas ini.');
        }

        $taskTitle = $task->title;

        // 2. Hapus data dari MySQL
        $task->delete();

        // 3. Respons AJAX
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tugas "' . $taskTitle . '" berhasil dihapus.',
            ]);
        }

        // 4. Redirect kembali dengan flash message
        return redirect()->back()->with('success', 'Tugas "' . $taskTitle . '" berhasil dihapus dari papan!');
    }

    /**
     * Menginisialisasi tugas-tugas awal secara otomatis jika akun pengguna masih belum memiliki data tugas.
     * Sangat berguna saat penguji mendaftar akun baru atau login dengan akun kosong.
     *
     * @param int $userId
     * @return void
     */
    public function seedStarterTasks(int $userId): void
    {
        $today = Carbon::today()->format('Y-m-d');
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $nextWeek = Carbon::today()->addDays(5)->format('Y-m-d');
        $nextTwoWeeks = Carbon::today()->addDays(14)->format('Y-m-d');

        $sampleTasks = [
            [
                'user_id' => $userId,
                'title' => 'Penyusunan Rencana Anggaran Q4',
                'description' => 'Merekapitulasi belanja operasional akhir tahun dan menyusun estimasi pendapatan.',
                'category' => 'Keuangan',
                'priority' => 'Tinggi',
                'status' => 'Belum Dikerjakan',
                'due_date' => $today,
                'subtasks' => [
                    ['title' => 'Rekap nota pengeluaran operasional', 'completed' => true],
                    ['title' => 'Input laporan rugi laba ke sistem', 'completed' => false],
                    ['title' => 'Kirim laporan ke pembimbing / manager', 'completed' => false],
                ],
                'subtasks_total' => 3,
                'subtasks_completed' => 1,
                'order' => 1,
            ],
            [
                'user_id' => $userId,
                'title' => 'Desain Prototipe Visual FlowTask',
                'description' => 'Merancang antarmuka visual untuk papan kanban, form modal, dan alur interaksi pengguna.',
                'category' => 'Desain UI/UX',
                'priority' => 'Tinggi',
                'status' => 'Belum Dikerjakan',
                'due_date' => $tomorrow,
                'subtasks' => [
                    ['title' => 'Eksplorasi wireframe antarmuka', 'completed' => true],
                    ['title' => 'Tentukan palet warna tema dan dark mode', 'completed' => true],
                    ['title' => 'Buat komponen kartu kanban', 'completed' => false],
                    ['title' => 'Uji responsivitas tablet dan mobile', 'completed' => false],
                ],
                'subtasks_total' => 4,
                'subtasks_completed' => 2,
                'order' => 2,
            ],
            [
                'user_id' => $userId,
                'title' => 'Riset Tren UI Kanban Modern',
                'description' => 'Melakukan benchmarking layout kartu kanban dari berbagai platform produktivitas global.',
                'category' => 'Riset',
                'priority' => 'Sedang',
                'status' => 'Belum Dikerjakan',
                'due_date' => $nextWeek,
                'subtasks' => [
                    ['title' => 'Analisis UX Trello dan Notion', 'completed' => true],
                    ['title' => 'Catat pola interaksi drag & drop terbaik', 'completed' => false],
                ],
                'subtasks_total' => 2,
                'subtasks_completed' => 1,
                'order' => 3,
            ],
            [
                'user_id' => $userId,
                'title' => 'Pembuatan Komponen Blade Modular',
                'description' => 'Memecah antarmuka monolitik menjadi komponen sidebar, navbar, kartu kanban, dan modal dialog.',
                'category' => 'Frontend',
                'priority' => 'Tinggi',
                'status' => 'Sedang Dikerjakan',
                'due_date' => $today,
                'subtasks' => [
                    ['title' => 'Komponen sidebar navigasi', 'completed' => true],
                    ['title' => 'Komponen navbar header & tema', 'completed' => true],
                    ['title' => 'Komponen modal edit tugas interaktif', 'completed' => true],
                ],
                'subtasks_total' => 3,
                'subtasks_completed' => 3,
                'order' => 1,
            ],
            [
                'user_id' => $userId,
                'title' => 'Implementasi Dark Mode & Mood Switcher',
                'description' => 'Menyediakan fitur Dark/Light mode instan dan variasi warna aksen menggunakan Alpine.js.',
                'category' => 'Desain UI/UX',
                'priority' => 'Sedang',
                'status' => 'Sedang Dikerjakan',
                'due_date' => $tomorrow,
                'subtasks' => [
                    ['title' => 'Script anti-FOUT dark mode awal', 'completed' => true],
                    ['title' => 'Dukungan tema emerald, rose, cyan, dan amber', 'completed' => true],
                ],
                'subtasks_total' => 2,
                'subtasks_completed' => 2,
                'order' => 2,
            ],
            [
                'user_id' => $userId,
                'title' => 'Integrasi Sistem Autentikasi',
                'description' => 'Memverifikasi kredensial login via Auth::attempt, regenerasi session, dan pengamanan kata sandi.',
                'category' => 'Backend',
                'priority' => 'Tinggi',
                'status' => 'Sedang Dikerjakan',
                'due_date' => $yesterday,
                'subtasks' => [
                    ['title' => 'Form login dengan validasi email', 'completed' => true],
                    ['title' => 'Form registrasi pengguna baru', 'completed' => true],
                    ['title' => 'Pengamanan session fixation & CSRF token', 'completed' => true],
                    ['title' => 'Fitur ingat saya (remember me)', 'completed' => false],
                ],
                'subtasks_total' => 4,
                'subtasks_completed' => 3,
                'order' => 3,
            ],
            [
                'user_id' => $userId,
                'title' => 'Setup Lingkungan PHP 8.4 Laragon',
                'description' => 'Menghubungkan binary PHP 8.4 Laragon dan memastikan kompatibilitas dependency framework.',
                'category' => 'Infrastruktur',
                'priority' => 'Rendah',
                'status' => 'Selesai',
                'due_date' => $yesterday,
                'subtasks' => [
                    ['title' => 'Download & ekstrak PHP 8.4 NTS', 'completed' => true],
                    ['title' => 'Uji CLI php -v dan composer -V', 'completed' => true],
                ],
                'subtasks_total' => 2,
                'subtasks_completed' => 2,
                'order' => 1,
            ],
            [
                'user_id' => $userId,
                'title' => 'Inisialisasi Proyek Laravel FlowTask',
                'description' => 'Mengonfigurasi basis framework Laravel, Tailwind CSS, skema migrasi tasks, dan struktur direktori view.',
                'category' => 'Core',
                'priority' => 'Sedang',
                'status' => 'Selesai',
                'due_date' => $nextTwoWeeks,
                'subtasks' => [
                    ['title' => 'Inisialisasi composer create-project', 'completed' => true],
                    ['title' => 'Konfigurasi koneksi MySQL .env', 'completed' => true],
                ],
                'subtasks_total' => 2,
                'subtasks_completed' => 2,
                'order' => 2,
            ],
        ];

        foreach ($sampleTasks as $taskData) {
            Task::create($taskData);
        }
    }
}
