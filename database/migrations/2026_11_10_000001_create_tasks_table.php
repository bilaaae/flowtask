<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->default('Pekerjaan');
            $table->enum('priority', ['Tinggi', 'Sedang', 'Rendah'])->default('Sedang');
            $table->enum('status', ['Belum Dikerjakan', 'Sedang Dikerjakan', 'Selesai'])->default('Belum Dikerjakan');
            $table->date('due_date')->nullable();
            $table->unsignedInteger('subtasks_total')->default(0);
            $table->unsignedInteger('subtasks_completed')->default(0);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
