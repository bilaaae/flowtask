<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * =========================================================================================
 * MODEL ELOQUENT: User (Pengguna Aplikasi)
 * =========================================================================================
 * Model ini menangani entitas pengguna yang memiliki akses login ke FlowTask.
 * Terhubung dengan tabel 'users' di MySQL dan menggunakan hashing password bawaan Laravel.
 * =========================================================================================
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Konversi tipe data otomatis untuk atribut model.
     * Password otomatis di-hash menggunakan algoritma Bcrypt/Argon2.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi Eloquent: Satu User dapat memiliki banyak Tugas (One-to-Many / HasMany).
     * Menghubungkan users.id dengan tasks.user_id.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
