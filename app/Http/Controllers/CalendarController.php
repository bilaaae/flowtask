<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * =========================================================================================
 * CONTROLLER: CalendarController (Pengendali Tampilan Agenda Kalender)
 * =========================================================================================
 * Controller ini mengambil seluruh data tugas pengguna yang memiliki tanggal deadline (due_date)
 * dari database MySQL untuk dirender ke dalam kalender interaktif bulanan di antarmuka web.
 * 
 * Penguji Ujian:
 * "Data apa yang diambil oleh CalendarController?"
 * Jawab: Mengambil data tugas pengguna dari tabel 'tasks' MySQL dengan filter user_id,
 * diurutkan berdasarkan tanggal tenggat (due_date asc), dan dikirim ke view 'calendar.blade.php'.
 * =========================================================================================
 */
class CalendarController extends Controller
{
    /**
     * Menampilkan halaman Agenda Kalender tugas.
     *
     * @return View
     */
    public function index(): View
    {
        // 1. Ambil seluruh data tugas milik user aktif dari MySQL
        $tasks = Task::where('user_id', Auth::id())
            ->whereNotNull('due_date')
            ->orderBy('due_date', 'asc')
            ->get();

        // 2. Kirim data ke file view resources/views/calendar.blade.php
        return view('calendar', compact('tasks'));
    }
}
