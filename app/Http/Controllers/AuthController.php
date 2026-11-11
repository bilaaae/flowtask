<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * =========================================================================================
 * CONTROLLER: AuthController (Pengendali Autentikasi Pengguna)
 * =========================================================================================
 * Mengelola sistem otentikasi akun: Login, Registrasi Pengguna Baru, Regenerasi Session,
 * dan Logout aman dengan proteksi CSRF.
 * 
 * Penguji Ujian:
 * "Bagaimana alur kerja sistem login di FlowTask?"
 * Jawab: Pengguna memasukkan email dan password -> Divalidasi oleh AuthController ->
 * Diperiksa kecocokannya dengan tabel 'users' di MySQL menggunakan Auth::attempt() (verifikasi hash Bcrypt) ->
 * Jika cocok, session di-regenerate untuk mencegah serangan Session Fixation dan dialihkan ke Dashboard.
 * =========================================================================================
 */
class AuthController extends Controller
{
    /**
     * Menampilkan form login (View: resources/views/auth/login.blade.php).
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Memproses autentikasi login pengguna ke database MySQL.
     */
    public function login(Request $request)
    {
        // 1. Validasi input email dan password
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        // 2. Verifikasi kecocokan kredensial dengan database MySQL
        if (Auth::attempt($credentials, $remember)) {
            // Amankan session dari pembajakan
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang kembali, ' . Auth::user()->name . '!');
        }

        // 3. Jika gagal, kembalikan dengan pesan error
        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => 'Email atau kata sandi yang Anda masukkan tidak sesuai dengan data kami.',
            ]);
    }

    /**
     * Menampilkan form registrasi pengguna baru (View: resources/views/auth/register.blade.php).
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Memproses registrasi pengguna baru dan menyimpan akun baru ke tabel 'users' MySQL.
     */
    public function register(Request $request)
    {
        // 1. Validasi input nama, email unik, dan konfirmasi password minimal 8 karakter
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah terdaftar. Silakan gunakan email lain atau masuk.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal harus terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        // 2. Simpan user baru dengan enkripsi password Hash::make
        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
        ]);

        // 3. Login otomatis dan regenerasi session
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Akun berhasil dibuat! Selamat datang di FlowTask.');
    }

    /**
     * Memproses logout pengguna, membatalkan session, dan regenerasi token CSRF.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'Anda telah berhasil keluar dari akun.');
    }
}
