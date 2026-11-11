<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Menampilkan halaman Proyek Saya.
     */
    public function index()
    {
        // Data proyek terstruktur untuk ruang kerja pengguna
        $projects = [
            [
                'id' => 1,
                'title' => 'Pengembangan Sistem FlowTask',
                'description' => 'Membangun platform manajemen tugas harian yang cepat, responsif, dan fleksibel dengan Kanban dan Kalender.',
                'category' => 'Pengembangan Web',
                'status' => 'Berjalan',
                'progress' => 75,
                'due_date' => '2026-11-25',
                'tasks_count' => 12,
                'completed_tasks' => 9,
                'color' => 'indigo',
            ],
            [
                'id' => 2,
                'title' => 'Redesain Antarmuka Mobile & Web',
                'description' => 'Menyempurnakan wireframe, sistem desain warna, tipografi, dan pengalaman pengguna interaktif.',
                'category' => 'Desain & Kreatif',
                'status' => 'Berjalan',
                'progress' => 50,
                'due_date' => '2026-11-30',
                'tasks_count' => 8,
                'completed_tasks' => 4,
                'color' => 'purple',
            ],
            [
                'id' => 3,
                'title' => 'Penyusunan Anggaran & Keuangan Q4',
                'description' => 'Merekapitulasi alokasi pengeluaran, proyeksi pendapatan, dan efisiensi operasional akhir tahun.',
                'category' => 'Keuangan',
                'status' => 'Perencanaan',
                'progress' => 25,
                'due_date' => '2026-12-15',
                'tasks_count' => 6,
                'completed_tasks' => 1,
                'color' => 'amber',
            ],
            [
                'id' => 4,
                'title' => 'Dokumentasi & Standar Operasional',
                'description' => 'Menyusun pedoman kerja tim, panduan rilis fitur baru, dan checklist arsitektur sistem.',
                'category' => 'Dokumentasi',
                'status' => 'Selesai',
                'progress' => 100,
                'due_date' => '2026-11-05',
                'tasks_count' => 5,
                'completed_tasks' => 5,
                'color' => 'emerald',
            ],
        ];

        return view('projects', compact('projects'));
    }
}
