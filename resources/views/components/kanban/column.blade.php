<!-- ========================================================================================= -->
<!-- KOMPONEN BLADE: Kolom Papan Kanban (Kanban Column)                                         -->
<!-- ========================================================================================= -->
<!-- Komponen ini mewakili 1 kolom status pada alur kerja ('Belum Dikerjakan',                  -->
<!-- 'Sedang Dikerjakan', atau 'Selesai'). Dilengkapi zona Drop HTML5 untuk menangani           -->
<!-- interaktivitas Drag-and-Drop kartu tugas secara langsung.                                  -->
<!--                                                                                            -->
<!-- Penguji Ujian:                                                                             -->
<!-- "Bagaimana cara kerja fitur Drag and Drop pada kolom ini?"                                 -->
<!-- Jawab: Kolom ini mendengarkan event dragover dan drop menggunakan directive Alpine.js     -->
<!-- (@dragover.prevent dan @drop.prevent). Saat kartu dilepas di kolom ini, method handleDrop()-->
<!-- dipanggil untuk mengirimkan request PATCH /tasks/{id}/status ke Laravel Controller.        -->
<!-- ========================================================================================= -->

@props([
    'title' => 'Belum Dikerjakan',
    'titleKey' => 'kolom_belum',
    'count' => 0,
    'color' => 'amber'
])

@php
    $dotColors = [
        'amber' => 'bg-amber-500 ring-amber-100 dark:ring-amber-950/60',
        'blue' => 'bg-blue-500 ring-blue-100 dark:ring-blue-950/60',
        'emerald' => 'bg-emerald-500 ring-emerald-100 dark:ring-emerald-950/60'
    ];
    $borderColors = [
        'amber' => 'border-amber-200/80 dark:border-amber-900/40',
        'blue' => 'border-blue-200/80 dark:border-blue-900/40',
        'emerald' => 'border-emerald-200/80 dark:border-emerald-900/40'
    ];
    $badgeColors = [
        'amber' => 'bg-amber-100 text-amber-800 border-amber-300/60 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800/60',
        'blue' => 'bg-blue-100 text-blue-800 border-blue-300/60 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800/60',
        'emerald' => 'bg-emerald-100 text-emerald-800 border-emerald-300/60 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800/60'
    ];
@endphp

<div x-data="{ isDragOver: false }"
     @dragover.prevent="isDragOver = true"
     @dragleave.prevent="isDragOver = false"
     @drop.prevent="isDragOver = false; handleDrop($event, '{{ $title }}')"
     :class="isDragOver ? 'ring-2 ring-indigo-500 border-indigo-400 bg-indigo-50/60 dark:bg-indigo-950/40' : ''"
     class="bg-slate-100/80 dark:bg-slate-900/60 rounded-2xl p-4 border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-4 transition-all duration-200 min-h-[350px]">
    
    <!-- 1. Header Kolom (Ikon Status, Judul, Jumlah Tugas, & Tombol Tambah Cepat) -->
    <div class="flex items-center justify-between pb-3 border-b {{ $borderColors[$color] ?? $borderColors['amber'] }}">
        <div class="flex items-center gap-2.5">
            <span class="w-3 h-3 rounded-full {{ $dotColors[$color] ?? $dotColors['amber'] }} ring-4"></span>
            <h3 class="font-bold text-slate-800 dark:text-slate-200 text-sm tracking-tight" x-text="t('{{ $titleKey }}')">{{ $title }}</h3>
            <!-- Badge Angka Jumlah Tugas di Kolom ini -->
            <span class="px-2 py-0.5 rounded-full text-xs font-bold border shadow-2xs {{ $badgeColors[$color] ?? $badgeColors['amber'] }}">{{ $count }}</span>
        </div>
        <!-- Tombol Tambah Tugas Baru langsung ke kolom ini -->
        <button type="button" 
                @click="isNewTaskModalOpen = true" 
                class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-white dark:hover:bg-slate-800 transition-colors"
                title="Tambah tugas ke kolom ini">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        </button>
    </div>

    <!-- 2. Area Konten Kartu Tugas (Slot Blade & Looping Kartu) -->
    <div class="space-y-3.5">
        {{ $slot }}
    </div>

    <!-- Tampilan Pembantu jika kolom masih kosong -->
    @if ($count === 0)
        <div class="py-8 text-center border border-dashed border-slate-200 dark:border-slate-800 rounded-xl text-slate-400 text-xs">
            <span>Tarik kartu ke sini atau klik + untuk menambah tugas</span>
        </div>
    @endif
</div>
