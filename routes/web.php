<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (Daftar Rute Web Aplikasi FlowTask)
|--------------------------------------------------------------------------
| File ini mendefinisikan seluruh pemetaan alamat URL (Uniform Resource Locator)
| ke method Controller yang bersangkutan.
|
| Penguji Ujian:
| "Bagaimana cara kerja sistem routing di Laravel?"
| Jawab: Setiap ada permintaan HTTP yang masuk dari browser (misal GET /agenda),
| file routes/web.php mencocokkan URL tersebut dengan rute yang terdaftar,
| menjalankan middleware keamanan (seperti memeriksa status login), lalu
| memanggil method fungsi di Controller yang bertugas merespons permintaan tersebut.
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| 1. Rute Khusus Tamu (Guest Routes)
|--------------------------------------------------------------------------
| Rute dalam grup ini hanya dapat diakses oleh pengunjung yang BELUM LOGIN.
| Jika user yang sudah login mencoba mengakses /login atau /register,
| middleware 'guest' akan otomatis mengalihkan mereka ke halaman Dashboard.
*/
Route::middleware('guest')->group(function () {
    
    // URL: GET /login -> Menampilkan halaman formulir login pengguna (AuthController@showLoginForm)
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

    // URL: POST /login -> Menerima input kredensial (email & password) dan memvalidasi ke database (AuthController@login)
    Route::post('/login', [AuthController::class, 'login']);

    // URL: GET /register -> Menampilkan formulir pendaftaran akun pengguna baru (AuthController@showRegisterForm)
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');

    // URL: POST /register -> Memproses pembuatan akun baru dan menyimpannya ke tabel 'users' MySQL (AuthController@register)
    Route::post('/register', [AuthController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| 2. Rute Terproteksi (Authenticated Routes)
|--------------------------------------------------------------------------
| Seluruh rute di bawah ini WAJIB LOGIN (Middleware 'auth').
| Jika pengguna belum login mencoba membuka URL ini, Laravel secara otomatis
| mengalihkan pengguna ke halaman login demi menjaga keamanan data pribadi.
*/
Route::middleware('auth')->group(function () {

    // URL: GET / -> Rute beranda utama yang langsung mengalihkan (redirect) pengguna ke halaman Dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | Fitur Papan Kanban & Operasi CRUD Tugas (TaskController)
    |--------------------------------------------------------------------------
    */

    // URL: GET /dashboard -> Membuka Papan Kanban dan menampilkan tugas terkelompok per kolom (TaskController@index)
    Route::get('/dashboard', [TaskController::class, 'index'])->name('dashboard');

    // URL: POST /tasks -> Menyimpan data tugas baru dan subtugas ke tabel 'tasks' MySQL (TaskController@store)
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');

    // URL: PUT /tasks/{task} -> Memperbarui rincian tugas dan status centang subtugas (TaskController@update)
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');

    // URL: PATCH /tasks/{task}/status -> Memperbarui status kolom tugas secara dinamis via Drag & Drop atau Dropdown (TaskController@updateStatus)
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');

    // URL: DELETE /tasks/{task} -> Menghapus data tugas dari database MySQL (TaskController@destroy)
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    /*
    |--------------------------------------------------------------------------
    | Fitur Tampilan Alternatif (Daftar Agenda & Kalender)
    |--------------------------------------------------------------------------
    */

    // URL: GET /agenda -> Menampilkan data tugas dalam bentuk baris daftar (List View) lengkap dengan sorting (AgendaController@index)
    Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda');

    // URL: GET /calendar -> Menampilkan agenda tugas yang dijadwalkan ke dalam antarmuka kalender bulanan (CalendarController@index)
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');

    // URL: GET /projects -> Alias tautan lama yang dialihkan secara mulus ke halaman Daftar Agenda
    Route::get('/projects', function () {
        return redirect()->route('agenda');
    })->name('projects');

    /*
    |--------------------------------------------------------------------------
    | Fitur Keluar Akun (Logout)
    |--------------------------------------------------------------------------
    */

    // URL: POST /logout -> Mengakhiri session login pengguna dan menghapus token autentikasi (AuthController@logout)
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
