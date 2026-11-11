<!-- ========================================================================================= -->
<!-- VIEW BLADE: dashboard.blade.php (Papan Kanban Interaktif FlowTask)                        -->
<!-- ========================================================================================= -->
<!-- Halaman utama aplikasi yang menampilkan tugas-tugas dalam 3 kolom Kanban:                 -->
<!-- 1. Belum Dikerjakan (Amber/Kuning)                                                        -->
<!-- 2. Sedang Dikerjakan (Blue/Biru)                                                          -->
<!-- 3. Selesai (Emerald/Hijau)                                                                -->
<!--                                                                                            -->
<!-- Fitur Utama & Logika:                                                                      -->
<!-- - Data dinamis dari database MySQL via TaskController@index                                -->
<!-- - Drag-and-Drop kartu antar kolom dengan sinkronisasi otomatis ke database via fetch/AJAX  -->
<!-- - Filter Prioritas Cepat (Semua, Tinggi, Sedang, Rendah) via Alpine.js                     -->
<!-- - Pencarian Judul Tugas Real-time via Alpine.js x-model="searchQuery"                      -->
<!-- - Modal Edit Tugas & Subtugas sungguhan (menggantikan alert() lama)                       -->
<!-- ========================================================================================= -->

<x-layouts.app :title="'FlowTask - Papan Kanban'">

    <div class="min-h-screen flex bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased selection:bg-indigo-500 selection:text-white transition-colors duration-200">
        
        <!-- ========================================== -->
        <!-- 1. Sidebar Navigasi Kiri (Modular)        -->
        <!-- ========================================== -->
        <x-sidebar active="dashboard" />

        <!-- ========================================== -->
        <!-- 2. Area Konten Utama                      -->
        <!-- ========================================== -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            
            <!-- Navbar Header Atas dengan Pengalih Tema, Mood Switcher, dan Tombol Tambah Tugas -->
            <x-navbar :title="'Manajemen Tugas'" 
                      :breadcrumb="'Papan Kanban'" 
                      :ctaText="'tambah_tugas'" 
                      :ctaAction="'isNewTaskModalOpen = true'" />

            <!-- Notifikasi Sukses Session (Flash Message dari Controller) -->
            @if (session('success'))
                <div class="mx-4 sm:mx-6 lg:mx-8 mt-4 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm flex items-center justify-between animate-fadeIn">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- 3. Papan Kanban 3 Kolom Responsif -->
            <main class="p-4 sm:p-6 lg:p-8 space-y-6">

                <!-- ========================================== -->
                <!-- Toolbar: Filter Prioritas & Pencarian Live -->
                <!-- ========================================== -->
                <!-- Menggunakan logika Alpine.js untuk menyaring tampilan kartu secara instan -->
                <div class="flex flex-wrap items-center justify-between gap-4 pb-2">
                    <!-- Tombol Filter Prioritas (Alpine.js selectedPriority) -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 mr-1" x-text="t('filter_prioritas')">Filter Prioritas:</span>
                        <button type="button" 
                                @click="selectedPriority = 'all'" 
                                :class="selectedPriority === 'all' ? 'bg-slate-800 dark:bg-slate-200 text-white dark:text-slate-900 font-bold' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700'"
                                class="px-3 py-1 rounded-lg text-xs font-medium transition-colors" 
                                x-text="t('semua')">Semua</button>
                        <button type="button" 
                                @click="selectedPriority = 'Tinggi'" 
                                :class="selectedPriority === 'Tinggi' ? 'bg-rose-600 text-white font-bold' : 'bg-white dark:bg-slate-800 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900/50 hover:bg-slate-50 dark:hover:bg-slate-700'"
                                class="px-3 py-1 rounded-lg text-xs font-medium transition-colors" 
                                x-text="t('tinggi')">Tinggi</button>
                        <button type="button" 
                                @click="selectedPriority = 'Sedang'" 
                                :class="selectedPriority === 'Sedang' ? 'bg-amber-600 text-white font-bold' : 'bg-white dark:bg-slate-800 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50 hover:bg-slate-50 dark:hover:bg-slate-700'"
                                class="px-3 py-1 rounded-lg text-xs font-medium transition-colors" 
                                x-text="t('sedang')">Sedang</button>
                        <button type="button" 
                                @click="selectedPriority = 'Rendah'" 
                                :class="selectedPriority === 'Rendah' ? 'bg-emerald-600 text-white font-bold' : 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50 hover:bg-slate-50 dark:hover:bg-slate-700'"
                                class="px-3 py-1 rounded-lg text-xs font-medium transition-colors" 
                                x-text="t('rendah')">Rendah</button>
                    </div>

                    <!-- Input Pencarian Real-Time & Indikator Cloud Sync -->
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <div class="relative flex-1 sm:w-60">
                            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" 
                                   x-model="searchQuery" 
                                   :placeholder="t('cari_tugas')" 
                                   class="w-full pl-9 pr-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs focus:outline-hidden focus:border-theme-dynamic shadow-2xs">
                        </div>

                        <span class="inline-flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium shrink-0">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> 
                            <span class="hidden md:inline">Database Terhubung</span>
                        </span>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- GRID KANBAN 3 KOLOM RESPONSIVE (DENGAN DATA NYATA DARI MYSQL)             -->
                <!-- ========================================================================= -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 items-start">

                    <!-- ============================================================== -->
                    <!-- KOLOM 1: BELUM DIKERJAKAN (AMBER / KUNING)                     -->
                    <!-- ============================================================== -->
                    <!-- Menerima drop kartu tugas dan memfilter tugas status 'Belum Dikerjakan' -->
                    <x-kanban.column :title="'Belum Dikerjakan'" :titleKey="'kolom_belum'" :count="$tasksTodo->count()" :color="'amber'">
                        <!-- Looping data tugas dari MySQL -->
                        @foreach ($tasksTodo as $task)
                            <x-kanban.card 
                                :id="$task->id"
                                :title="$task->title"
                                :category="$task->category"
                                :categoryClass="'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/50 dark:text-purple-300 dark:border-purple-800/60'"
                                :description="$task->description ?? ''"
                                :priority="$task->priority"
                                :status="$task->status"
                                :dueDate="$task->due_date ? $task->due_date->format('d M') : ''"
                                :fullDueDate="$task->due_date ? $task->due_date->format('d M Y') : ''"
                                :rawDueDate="$task->due_date ? $task->due_date->format('Y-m-d') : ''"
                                :subtasks="$task->subtasks ?? []"
                                :subtasksTotal="$task->subtasks_total"
                                :subtasksCompleted="$task->subtasks_completed"
                                :progress="$task->progress_percentage"
                                :isToday="$task->is_due_today"
                                :isTomorrow="$task->is_due_tomorrow"
                                :isOverdue="$task->is_overdue" />
                        @endforeach
                    </x-kanban.column>

                    <!-- ============================================================== -->
                    <!-- KOLOM 2: SEDANG DIKERJAKAN (BLUE / BIRU)                       -->
                    <!-- ============================================================== -->
                    <!-- Menampilkan tugas yang sedang aktif dikerjakan oleh pengguna    -->
                    <x-kanban.column :title="'Sedang Dikerjakan'" :titleKey="'kolom_sedang'" :count="$tasksInProgress->count()" :color="'blue'">
                        <!-- Looping data tugas dari MySQL -->
                        @foreach ($tasksInProgress as $task)
                            <x-kanban.card 
                                :id="$task->id"
                                :title="$task->title"
                                :category="$task->category"
                                :categoryClass="'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/50 dark:text-sky-300 dark:border-sky-800/60'"
                                :description="$task->description ?? ''"
                                :priority="$task->priority"
                                :status="$task->status"
                                :dueDate="$task->due_date ? $task->due_date->format('d M') : ''"
                                :fullDueDate="$task->due_date ? $task->due_date->format('d M Y') : ''"
                                :rawDueDate="$task->due_date ? $task->due_date->format('Y-m-d') : ''"
                                :subtasks="$task->subtasks ?? []"
                                :subtasksTotal="$task->subtasks_total"
                                :subtasksCompleted="$task->subtasks_completed"
                                :progress="$task->progress_percentage"
                                :isToday="$task->is_due_today"
                                :isTomorrow="$task->is_due_tomorrow"
                                :isOverdue="$task->is_overdue" />
                        @endforeach
                    </x-kanban.column>

                    <!-- ============================================================== -->
                    <!-- KOLOM 3: SELESAI (EMERALD / HIJAU)                             -->
                    <!-- ============================================================== -->
                    <!-- Menampilkan tugas yang telah rampung dan tuntas 100%           -->
                    <x-kanban.column :title="'Selesai'" :titleKey="'kolom_selesai'" :count="$tasksDone->count()" :color="'emerald'">
                        <!-- Looping data tugas dari MySQL -->
                        @foreach ($tasksDone as $task)
                            <x-kanban.card 
                                :id="$task->id"
                                :title="$task->title"
                                :category="$task->category"
                                :categoryClass="'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800/60'"
                                :description="$task->description ?? ''"
                                :priority="$task->priority"
                                :status="$task->status"
                                :dueDate="$task->due_date ? $task->due_date->format('d M') : ''"
                                :fullDueDate="$task->due_date ? $task->due_date->format('d M Y') : ''"
                                :rawDueDate="$task->due_date ? $task->due_date->format('Y-m-d') : ''"
                                :subtasks="$task->subtasks ?? []"
                                :subtasksTotal="$task->subtasks_total"
                                :subtasksCompleted="$task->subtasks_completed"
                                :progress="$task->progress_percentage"
                                :isToday="$task->is_due_today"
                                :isTomorrow="$task->is_due_tomorrow"
                                :isOverdue="$task->is_overdue" />
                        @endforeach
                    </x-kanban.column>

                </div>

            </main>

        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 4. Komponen Modals Dialog (Create, Read, Update, Delete)                  -->
    <!-- ========================================================================= -->
    <!-- Modal Tambah Tugas Baru (Create CRUD) -->
    <x-modals.new-task />

    <!-- Modal Edit Tugas & Subtugas Sungguhan (Update CRUD) -->
    <x-modals.edit-task />

    <!-- Modal Lihat Rincian Tugas (Read View) -->
    <x-modals.task-detail />

    <!-- Modal Konfirmasi Hapus Tugas (Delete CRUD) -->
    <x-modals.delete-task />

</x-layouts.app>
