<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi untuk menambahkan kolom subtasks pada tabel tasks.
     * Kolom ini bertipe JSON agar dapat menyimpan daftar subtugas (judul & status centang/selesai).
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Kolom JSON untuk menyimpan rincian subtugas berupa array: [{"title": "...", "completed": true/false}]
            $table->json('subtasks')->nullable()->after('subtasks_completed');
        });
    }

    /**
     * Membatalkan migrasi (rollback) dengan menghapus kolom subtasks.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('subtasks');
        });
    }
};
