<!-- ========================================================================================= -->
<!-- KOMPONEN BLADE: Kartu Tugas Kanban (Kanban Card Component)                                 -->
<!-- ========================================================================================= -->
<!-- Komponen kartu interaktif yang mewakili 1 baris data tugas dari tabel 'tasks' MySQL.        -->
<!-- Mendukung:                                                                                 -->
<!-- 1. HTML5 Drag-and-Drop ke kolom status lain dengan update otomatis ke database MySQL       -->
<!-- 2. Tombol Pindah Status langsung di menu dropdown titik tiga                               -->
<!-- 3. Tombol Edit Tugas & Subtugas sungguhan (membuka modal edit terhubung ke Controller)    -->
<!-- 4. Progress bar dinamis berdasarkan rasio subtugas yang dicentang                          -->
<!-- 5. Sistem Pengingat Deadline (Badge merah menyala jika Hari ini, oranye jika Besok)        -->
<!-- ========================================================================================= -->

@props([
    'id' => 0,
    'title' => '',
    'category' => 'Umum',
    'categoryClass' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/50 dark:text-purple-300 dark:border-purple-800/60',
    'description' => '',
    'priority' => 'Sedang',
    'status' => 'Belum Dikerjakan',
    'dueDate' => '',
    'fullDueDate' => '',
    'rawDueDate' => '',
    'subtasks' => [],
    'subtasksTotal' => 0,
    'subtasksCompleted' => 0,
    'progress' => 0,
    'isToday' => false,
    'isTomorrow' => false,
    'isOverdue' => false
])

@php
    // Pewarnaan lencana prioritas
    $priorityClass = match($priority) {
        'Tinggi' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-900/50',
        'Sedang' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-900/50',
        'Rendah' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-900/50',
        default => 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'
    };
    $priorityKey = match($priority) {
        'Tinggi' => 'tinggi',
        'Sedang' => 'sedang',
        'Rendah' => 'rendah',
        default => 'sedang'
    };

    // Objek task lengkap untuk dioper ke modal edit atau detail
    $taskJson = json_encode([
        'id' => $id,
        'title' => $title,
        'category' => $category,
        'description' => $description,
        'priority' => $priority,
        'status' => $status,
        'due_date' => $rawDueDate ?: $dueDate,
        'subtasks' => is_array($subtasks) ? $subtasks : []
    ]);
@endphp

<!-- Elemen Kartu dengan dukungan Draggable HTML5 -->
<div draggable="true"
     @dragstart="handleDragStart($event, {{ $id }})"
     @dragend="handleDragEnd($event)"
     x-show="(selectedPriority === 'all' || selectedPriority === '{{ $priority }}') && ('{{ strtolower($title) }}'.includes(searchQuery.toLowerCase()))"
     class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200/90 dark:border-slate-700 shadow-2xs hover:shadow-md hover:border-slate-300 dark:hover:border-slate-600 transition-all duration-200 group relative cursor-grab active:cursor-grabbing">
    
    <!-- 1. Baris Atas: Kategori & Menu Dropdown Aksi -->
    <div class="flex items-center justify-between gap-2 mb-2.5">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-semibold border {{ $categoryClass }}">
            {{ $category }}
        </span>

        <div class="relative">
            <!-- Tombol Titik Tiga (Aksi) -->
            <button type="button" 
                    @click.stop="toggleDropdown('task-{{ $id }}')" 
                    class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
            </button>

            <!-- Dropdown Menu CRUD & Pindah Status -->
            <div x-show="activeDropdown === 'task-{{ $id }}'" 
                 x-cloak 
                 @click.away="closeDropdown()" 
                 class="absolute right-0 top-8 w-52 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 py-1.5 z-30 text-xs animate-fadeIn">
                
                <!-- Aksi 1: Lihat Detail -->
                <button type="button" 
                        @click="openDetail('{{ addslashes($title) }}', '{{ addslashes($category) }}', '{{ addslashes($description) }}', '{{ $priority }}', '{{ addslashes($status) }}', '{{ $fullDueDate }}', {{ $id }})" 
                        class="w-full text-left px-3 py-2 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    <span x-text="t('lihat_detail')">Lihat Detail</span>
                </button>

                <!-- Aksi 2: Edit Tugas & Subtugas (Membuka Modal Edit Sungguhan!) -->
                <button type="button" 
                        @click='openEditModal({{ $taskJson }})' 
                        class="w-full text-left px-3 py-2 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span x-text="t('edit_tugas')">Edit Tugas</span>
                </button>

                <!-- Aksi 3: Pilihan Cepat Pindah Status Kolom -->
                <div class="border-t border-slate-100 dark:border-slate-700 my-1 pt-1">
                    <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Pindah Kolom:</div>
                    @if ($status !== 'Belum Dikerjakan')
                        <button type="button" 
                                @click="moveTaskStatus({{ $id }}, 'Belum Dikerjakan')" 
                                class="w-full text-left px-3 py-1.5 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 flex items-center gap-2">
                            <span>➜ Belum Dikerjakan</span>
                        </button>
                    @endif
                    @if ($status !== 'Sedang Dikerjakan')
                        <button type="button" 
                                @click="moveTaskStatus({{ $id }}, 'Sedang Dikerjakan')" 
                                class="w-full text-left px-3 py-1.5 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 flex items-center gap-2">
                            <span>➜ Sedang Dikerjakan</span>
                        </button>
                    @endif
                    @if ($status !== 'Selesai')
                        <button type="button" 
                                @click="moveTaskStatus({{ $id }}, 'Selesai')" 
                                class="w-full text-left px-3 py-1.5 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 flex items-center gap-2">
                            <span>➜ Selesai</span>
                        </button>
                    @endif
                </div>

                <!-- Aksi 4: Hapus Tugas -->
                <button type="button" 
                        @click="confirmDelete('{{ addslashes($title) }}', {{ $id }})" 
                        class="w-full text-left px-3 py-2 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 flex items-center gap-2 border-t border-slate-100 dark:border-slate-700">
                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    <span x-text="t('hapus_tugas')">Hapus Tugas</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 2. Judul & Deskripsi Tugas -->
    <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100 leading-snug group-hover:text-theme-dynamic transition-colors cursor-pointer"
        @click='openEditModal({{ $taskJson }})'>
        {{ $title }}
    </h4>
    <p class="text-xs text-slate-500 dark:text-slate-300 mt-1.5 line-clamp-2 leading-relaxed">
        {{ $description }}
    </p>

    <!-- 3. Progress Bar Dinamis & Jumlah Subtugas -->
    @if ($subtasksTotal > 0 || $progress > 0)
        <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-700/80 space-y-1.5">
            <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <span>Subtugas: <strong class="text-slate-700 dark:text-slate-300">{{ $subtasksCompleted }}/{{ $subtasksTotal }}</strong></span>
                </span>
                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $progress }}%</span>
            </div>
            <!-- Visual Bar Kemajuan -->
            <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                <div class="h-1.5 rounded-full transition-all duration-500 {{ $progress === 100 ? 'bg-emerald-500' : 'bg-theme-dynamic' }}" 
                     style="width: {{ $progress }}%"></div>
            </div>
        </div>
    @endif

    <!-- 4. Baris Bawah: Prioritas & Sistem Pengingat Batas Waktu (Wanti-wanti) -->
    <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between text-xs">
        <!-- Badge Tingkat Prioritas -->
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold border {{ $priorityClass }}" x-text="t('{{ $priorityKey }}')">
            {{ $priority }}
        </span>

        <!-- Badge Sistem Pengingat Deadline (Wanti-wanti Mencolok) -->
        @if ($isToday)
            <!-- Peringatan Mendesak HARI INI: Merah Menyala + Animasi Pulse -->
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-red-500 text-white shadow-xs animate-pulse" title="Batas waktu hari ini!">
                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <span>Hari ini</span>
            </span>
        @elseif ($isTomorrow)
            <!-- Peringatan BESOK: Oranye Menyala -->
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500 text-white shadow-xs" title="Batas waktu besok!">
                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>Besok</span>
            </span>
        @elseif ($isOverdue)
            <!-- Terlambat / Lewat Deadline: Merah Gelap -->
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-700 text-white shadow-xs" title="Tugas telah melewati tenggat waktu">
                <span>Terlambat</span>
            </span>
        @elseif ($dueDate)
            <!-- Tanggal Biasa Mendatang -->
            <span class="text-slate-500 dark:text-slate-400 font-medium text-[11px] flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                {{ $dueDate }}
            </span>
        @endif
    </div>
</div>
