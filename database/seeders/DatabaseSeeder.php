<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * =========================================================================================
 * SEEDER: DatabaseSeeder (Pengisi Data Awal untuk Ujian Praktek)
 * =========================================================================================
 * Mengisi akun pengguna default dan beberapa contoh tugas nyata dengan tenggat waktu
 * variatif (Hari ini, Besok, Lewat tenggat, dan Mendatang) serta checklist subtugas
 * agar saat diuji oleh penguji, Papan Kanban dan Daftar Agenda langsung tampil sempurna.
 * =========================================================================================
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Menjalankan pengisian data awal ke database MySQL.
     */
    public function run(): void
    {
        // 1. Pastikan user default Nabila tersedia
        User::firstOrCreate(
            ['email' => 'nabila@flowtask.test'],
            [
                'name' => 'Nabila',
                'password' => Hash::make('password'),
            ]
        );

        $today = Carbon::today()->format('Y-m-d');
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $nextWeek = Carbon::today()->addDays(5)->format('Y-m-d');
        $nextTwoWeeks = Carbon::today()->addDays(14)->format('Y-m-d');

        // Template tugas percontohan
        $sampleTasksTemplate = [
            [
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

        // 2. Terapkan tugas untuk SETIAP pengguna yang terdaftar di database
        foreach (User::all() as $usr) {
            foreach ($sampleTasksTemplate as $tmpl) {
                Task::firstOrCreate(
                    [
                        'user_id' => $usr->id,
                        'title' => $tmpl['title'],
                    ],
                    array_merge($tmpl, ['user_id' => $usr->id])
                );
            }
        }
    }
}
