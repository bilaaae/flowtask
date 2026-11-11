<!-- ========================================================================================= -->
<!-- VIEW BLADE: agenda.blade.php (Halaman Daftar Agenda / List View FlowTask)                 -->
<!-- ========================================================================================= -->
<!-- Halaman ini menyajikan tugas/agenda dalam bentuk daftar baris ke bawah (List View)        -->
<!-- yang efisien, dilengkapi:                                                                 -->
<!-- 1. Sistem Pengingat Deadline (Badge peringatan merah menyala untuk "Hari ini" dan          -->
<!--    oranye menyala untuk "Besok" sebagai wanti-wanti tugas mendesak)                        -->
<!-- 2. Form Modal Edit sungguhan yang terhubung ke Controller Laravel (tanpa alert() lagi!)   -->
<!-- 3. Progress bar dinamis berdasarkan rasio subtugas yang dicentang                          -->
<!-- 4. Filter status & prioritas interaktif serta sorting deadline via Alpine.js               -->
<!-- ========================================================================================= -->

<x-layouts.app :title="'FlowTask - Daftar Agenda'">

    <div class="min-h-screen flex bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased selection:bg-indigo-500 selection:text-white transition-colors duration-200"
         x-data="{
             isNewAgendaModalOpen: false,
             isAgendaDetailModalOpen: false,
             agendaSearch: '',
             statusFilter: 'all',
             priorityFilter: 'all',
             sortBy: 'deadline_asc',
             activeAgenda: {
                 id: null,
                 title: '',
                 category: '',
                 description: '',
                 status: '',
                 priority: '',
                 progress: 0,
                 dueDate: '',
                 tasksCount: 0,
                 completedTasks: 0
             },
             openAgendaDetail(agenda) {
                 this.activeAgenda = agenda;
                 this.isAgendaDetailModalOpen = true;
             },
             // Data agenda diambil dari controller Laravel (Eloquent Model Task)
             agendas: {{ Js::from($agendas) }},

             // FILTER & PENGURUTAN REAKTIF (ALPINE.JS COMPUTED PROPERTY)
             get filteredAgendas() {
                 let list = this.agendas.filter(item => {
                     const matchSearch = item.title.toLowerCase().includes(this.agendaSearch.toLowerCase()) || 
                                         item.category.toLowerCase().includes(this.agendaSearch.toLowerCase()) ||
                                         item.description.toLowerCase().includes(this.agendaSearch.toLowerCase());
                     
                     // Pencocokan status fleksibel (mendukung status 'Belum Dikerjakan' & 'Sedang Dikerjakan')
                     const matchStatus = this.statusFilter === 'all' || 
                                         item.status === this.statusFilter ||
                                         (this.statusFilter === 'Sedang Dikerjakan' && item.status === 'Berjalan') ||
                                         (this.statusFilter === 'Berjalan' && item.status === 'Sedang Dikerjakan') ||
                                         (this.statusFilter === 'Belum Dikerjakan' && item.status === 'Perencanaan') ||
                                         (this.statusFilter === 'Perencanaan' && item.status === 'Belum Dikerjakan');

                     const matchPriority = this.priorityFilter === 'all' || item.priority === this.priorityFilter;
                     return matchSearch && matchStatus && matchPriority;
                 });

                 // Pengurutan (Sorting)
                 if (this.sortBy === 'deadline_asc') {
                     list.sort((a, b) => new Date(a.due_date || '9999-12-31') - new Date(b.due_date || '9999-12-31'));
                 } else if (this.sortBy === 'deadline_desc') {
                     list.sort((a, b) => new Date(b.due_date || '1000-01-01') - new Date(a.due_date || '1000-01-01'));
                 } else if (this.sortBy === 'progress_desc') {
                     list.sort((a, b) => b.progress - a.progress);
                 } else if (this.sortBy === 'title_asc') {
                     list.sort((a, b) => a.title.localeCompare(b.title));
                 }
                 return list;
             },

             // =========================================================================
             // SISTEM PENGINGAT DEADLINE (WANTI-WANTI MENDESAK)
             // =========================================================================
             // Fungsi ini menghitung selisih hari batas waktu dari hari ini.
             // - Jika 'Hari ini' -> Badge Merah Menyala (bg-red-500) + Animasi Pulse
             // - Jika 'Besok'    -> Badge Oranye Menyala (bg-amber-500)
             // - Jika Terlambat  -> Badge Rose Tua (bg-rose-700)
             // =========================================================================
             getDaysRemaining(dueDateStr) {
                 if (!dueDateStr) {
                     return { text: 'Tanpa batas waktu', badgeClass: 'bg-slate-100 dark:bg-slate-800 text-slate-500' };
                 }

                 const today = new Date();
                 today.setHours(0, 0, 0, 0);

                 // Normalisasi format tanggal YYYY-MM-DD aman lintas browser dan zona waktu
                 const cleanDateStr = dueDateStr.includes('T') ? dueDateStr.split('T')[0] : dueDateStr;
                 const parts = cleanDateStr.split('-');
                 const due = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
                 due.setHours(0, 0, 0, 0);
                 
                 const diffTime = due.getTime() - today.getTime();
                 const diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24));

                 if (diffDays < 0) {
                     return { 
                         text: '🚨 Lewat ' + Math.abs(diffDays) + ' hari', 
                         isOverdue: true, 
                         badgeClass: 'bg-rose-700 text-white font-extrabold shadow-sm' 
                     };
                 } else if (diffDays === 0) {
                     return { 
                         text: '⚠️ Hari ini (Mendesak!)', 
                         isUrgent: true, 
                         badgeClass: 'bg-red-500 text-white font-extrabold shadow-md animate-pulse ring-2 ring-red-300 dark:ring-red-900' 
                     };
                 } else if (diffDays === 1) {
                     return { 
                         text: '⏰ Besok (Segera)', 
                         isUrgent: true, 
                         badgeClass: 'bg-amber-500 text-white font-extrabold shadow-sm ring-2 ring-amber-300 dark:ring-amber-900' 
                     };
                 } else {
                     return { 
                         text: '📅 ' + diffDays + ' hari lagi', 
                         isOverdue: false, 
                         badgeClass: 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold' 
                     };
                 }
             }
         }">
        
        <!-- 1. Sidebar Navigasi Kiri (Aktif di Halaman Agenda) -->
        <x-sidebar active="agenda" />

        <!-- 2. Area Konten Utama -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            
            <!-- Navbar Header Atas dengan Tombol Tambah Agenda Baru -->
            <x-navbar :title="'Daftar Agenda'" 
                      :breadcrumb="'Daftar Agenda'" 
                      :ctaText="'tambah_agenda'" 
                      :ctaAction="'isNewAgendaModalOpen = true'" />

            <!-- Notifikasi Sukses Session dari Controller -->
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

            <!-- Main Content: Daftar Agenda (List View) -->
            <main class="p-4 sm:p-6 lg:p-8 space-y-6">

                <!-- Ringkasan Statistik Agenda -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs">
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Agenda</span>
                        <div class="flex items-baseline justify-between mt-2">
                            <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white" x-text="agendas.length">0</span>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Item Terdaftar</span>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs">
                        <span class="text-xs text-amber-600 dark:text-amber-400 font-medium">Belum Dikerjakan</span>
                        <div class="flex items-baseline justify-between mt-2">
                            <span class="text-2xl sm:text-3xl font-extrabold text-amber-600 dark:text-amber-400" x-text="agendas.filter(a => a.status === 'Belum Dikerjakan' || a.status === 'Perencanaan').length">0</span>
                            <span class="text-xs font-semibold text-amber-600/70 dark:text-amber-400/70">Mendatang</span>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs">
                        <span class="text-xs text-blue-600 dark:text-blue-400 font-medium">Sedang Dikerjakan</span>
                        <div class="flex items-baseline justify-between mt-2">
                            <span class="text-2xl sm:text-3xl font-extrabold text-blue-600 dark:text-blue-400" x-text="agendas.filter(a => a.status === 'Sedang Dikerjakan' || a.status === 'Berjalan').length">0</span>
                            <span class="text-xs font-semibold text-blue-600/70 dark:text-blue-400/70">Aktif</span>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs">
                        <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Selesai</span>
                        <div class="flex items-baseline justify-between mt-2">
                            <span class="text-2xl sm:text-3xl font-extrabold text-emerald-600 dark:text-emerald-400" x-text="agendas.filter(a => a.status === 'Selesai').length">0</span>
                            <span class="text-xs font-semibold text-emerald-600/70 dark:text-emerald-400/70">Tuntas</span>
                        </div>
                    </div>
                </div>

                <!-- Toolbar Filter & Fitur Pengurutan (Sort by Deadline) -->
                <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-4">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        
                        <!-- Input Pencarian Real-Time (Alpine.js x-model="agendaSearch") -->
                        <div class="relative flex-1 max-w-md">
                            <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" 
                                   x-model="agendaSearch" 
                                   :placeholder="t('cari_agenda')" 
                                   class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-hidden focus:border-theme-dynamic transition-colors">
                        </div>

                        <!-- Dropdown Pengurutan (Sort by Deadline / Progress / Nama) -->
                        <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                                </svg>
                                <span class="text-xs font-semibold text-slate-600 dark:text-slate-300" x-text="t('urutkan')">Urutkan:</span>
                            </div>
                            <select x-model="sortBy" 
                                    class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-xs font-semibold focus:outline-hidden focus:border-theme-dynamic shadow-2xs transition-colors cursor-pointer">
                                <option value="deadline_asc">📅 Deadline Terdekat (Asc)</option>
                                <option value="deadline_desc">📅 Deadline Terjauh (Desc)</option>
                                <option value="progress_desc">📈 Kemajuan Tertinggi</option>
                                <option value="title_asc">🔤 Nama Agenda (A - Z)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Filter Status & Prioritas Cepat -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-slate-500 dark:text-slate-400 font-medium mr-1">Status:</span>
                            <button type="button" 
                                    @click="statusFilter = 'all'" 
                                    :class="statusFilter === 'all' ? 'bg-theme-dynamic text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                                    class="px-3 py-1 rounded-lg transition-colors">Semua</button>
                            <button type="button" 
                                    @click="statusFilter = 'Belum Dikerjakan'" 
                                    :class="statusFilter === 'Belum Dikerjakan' ? 'bg-amber-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                                    class="px-3 py-1 rounded-lg transition-colors">Belum Dikerjakan</button>
                            <button type="button" 
                                    @click="statusFilter = 'Sedang Dikerjakan'" 
                                    :class="statusFilter === 'Sedang Dikerjakan' ? 'bg-blue-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                                    class="px-3 py-1 rounded-lg transition-colors">Sedang Dikerjakan</button>
                            <button type="button" 
                                    @click="statusFilter = 'Selesai'" 
                                    :class="statusFilter === 'Selesai' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                                    class="px-3 py-1 rounded-lg transition-colors">Selesai</button>
                        </div>

                        <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400 text-xs">
                            <span x-text="filteredAgendas.length + ' agenda ditampilkan'"></span>
                        </div>
                    </div>
                </div>

                <!-- ===================================================================== -->
                <!-- LIST VIEW: DAFTAR BARIS KE BAWAH (TABEL DATA AGENDANYA)              -->
                <!-- ===================================================================== -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-2xs overflow-hidden">
                    
                    <!-- Table Header (Tampilan Layar Desktop) -->
                    <div class="hidden lg:grid grid-cols-12 gap-4 px-6 py-3.5 bg-slate-50/80 dark:bg-slate-800 border-b border-slate-200/80 dark:border-slate-700 text-[11px] font-bold text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                        <div class="col-span-5">Agenda &amp; Kategori</div>
                        <div class="col-span-2">Prioritas &amp; Status</div>
                        <div class="col-span-2">Batas Waktu (Deadline)</div>
                        <div class="col-span-2">Kemajuan (Subtugas)</div>
                        <div class="col-span-1 text-right">Aksi</div>
                    </div>

                    <!-- Looping Baris Daftar Agenda (Alpine.js x-for) -->
                    <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        <template x-for="agenda in filteredAgendas" :key="agenda.id">
                            <div class="p-4 sm:p-5 lg:px-6 lg:py-4.5 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors group">
                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
                                    
                                    <!-- Kolom 1: Judul, Deskripsi, & Kategori -->
                                    <div class="lg:col-span-5 space-y-1.5">
                                        <div class="flex items-center gap-2.5 flex-wrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800" 
                                                  x-text="agenda.category"></span>
                                            
                                            <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white group-hover:text-theme-dynamic transition-colors cursor-pointer"
                                                @click="openEditModal(agenda)"
                                                x-text="agenda.title"></h3>
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1 leading-relaxed" 
                                           x-text="agenda.description"></p>
                                    </div>

                                    <!-- Kolom 2: Prioritas & Status -->
                                    <div class="lg:col-span-2 flex items-center gap-2">
                                        <!-- Badge Prioritas -->
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border"
                                              :class="{
                                                  'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-900/50': agenda.priority === 'Tinggi',
                                                  'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-900/50': agenda.priority === 'Sedang',
                                                  'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-900/50': agenda.priority === 'Rendah'
                                              }"
                                              x-text="agenda.priority"></span>

                                        <!-- Badge Status -->
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold"
                                              :class="{
                                                  'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300': agenda.status === 'Sedang Dikerjakan' || agenda.status === 'Berjalan',
                                                  'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300': agenda.status === 'Belum Dikerjakan' || agenda.status === 'Perencanaan',
                                                  'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300': agenda.status === 'Selesai'
                                              }">
                                            <span class="w-1.5 h-1.5 rounded-full"
                                                  :class="{
                                                      'bg-blue-500': agenda.status === 'Sedang Dikerjakan' || agenda.status === 'Berjalan',
                                                      'bg-amber-500': agenda.status === 'Belum Dikerjakan' || agenda.status === 'Perencanaan',
                                                      'bg-emerald-500': agenda.status === 'Selesai'
                                                  }"></span>
                                            <span x-text="agenda.status"></span>
                                        </span>
                                    </div>

                                    <!-- ========================================================================= -->
                                    <!-- Kolom 3: Batas Waktu (Deadline) dengan Sistem Pengingat Wanti-Wanti       -->
                                    <!-- ========================================================================= -->
                                    <div class="lg:col-span-2 space-y-1.5">
                                        <div class="flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 font-semibold">
                                            <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span x-text="agenda.due_date || 'Belum ditentukan'"></span>
                                        </div>
                                        <!-- Badge Lencana Peringatan (Merah Menyala untuk Hari Ini, Oranye Menyala untuk Besok) -->
                                        <div>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] leading-tight"
                                                  :class="getDaysRemaining(agenda.due_date).badgeClass"
                                                  x-text="getDaysRemaining(agenda.due_date).text"></span>
                                        </div>
                                    </div>

                                    <!-- Kolom 4: Kemajuan (Progress Bar Dinamis Berdasarkan Subtugas Selesai) -->
                                    <div class="lg:col-span-2 space-y-1.5">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-slate-500 dark:text-slate-400 text-[11px]">
                                                <span x-text="agenda.completed_tasks + '/' + agenda.tasks_count + ' subtugas'"></span>
                                            </span>
                                            <span class="font-bold text-slate-800 dark:text-slate-200" x-text="agenda.progress + '%'"></span>
                                        </div>
                                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                            <div class="h-2 rounded-full transition-all duration-500"
                                                 :class="{
                                                     'bg-emerald-500': agenda.progress === 100,
                                                     'bg-theme-dynamic': agenda.progress < 100
                                                 }"
                                                 :style="'width: ' + agenda.progress + '%'"></div>
                                        </div>
                                    </div>

                                    <!-- ========================================================================= -->
                                    <!-- Kolom 5: Tombol Aksi (Detail, Edit Modal Sungguhan, & Hapus)              -->
                                    <!-- ========================================================================= -->
                                    <div class="lg:col-span-1 flex items-center justify-end gap-1.5">
                                        <!-- Tombol Lihat Detail -->
                                        <button type="button" 
                                                @click="openAgendaDetail(agenda)"
                                                class="p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                                title="Lihat Detail Agenda">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        
                                        <!-- Tombol Edit Agenda (MODAL EDIT SUNGGUHAN: alert() lama telah dihapus!) -->
                                        <button type="button" 
                                                @click="openEditModal(agenda)"
                                                class="p-2 rounded-xl text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition-colors"
                                                title="Edit Agenda &amp; Kelola Subtugas">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <!-- Tombol Hapus Agenda -->
                                        <button type="button" 
                                                @click="confirmDelete(agenda)"
                                                class="p-2 rounded-xl text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                                                title="Hapus Agenda">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </template>

                        <!-- Tampilan Kosong jika filter pencarian tidak menemukan hasil -->
                        <div x-show="filteredAgendas.length === 0" class="p-12 text-center space-y-3">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mx-auto">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300">Tidak ada agenda yang cocok</h4>
                            <p class="text-xs text-slate-400 max-w-sm mx-auto">Coba ubah kata kunci pencarian, filter status, atau filter prioritas Anda.</p>
                        </div>
                    </div>

                </div>

            </main>

        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 4. Modal Tambah Agenda Baru (Create CRUD terhubung ke tasks.store)         -->
    <!-- ========================================================================= -->
    <div x-show="isNewAgendaModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         role="dialog" 
         aria-modal="true">
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity" 
             @click="isNewAgendaModalOpen = false"></div>
        <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg p-6 sm:p-7 space-y-5">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-theme-dynamic/10 dark:bg-theme-dynamic/20 text-theme-dynamic flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white" x-text="t('tambah_agenda')">Tambah Agenda Baru</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Buat rencana dan agenda produktivitas baru ke database.</p>
                        </div>
                    </div>
                    <button type="button" @click="isNewAgendaModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Form Tambah Agenda -> routes tasks.store -->
                <form action="{{ route('tasks.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Nama Agenda <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" required placeholder="Contoh: Rilis Modul Otomasi FlowTask" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 text-xs sm:text-sm focus:outline-hidden focus:border-theme-dynamic transition-colors">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Kategori</label>
                            <input type="text" name="category" placeholder="Contoh: Frontend, Desain" value="Pengembangan Web" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 text-xs sm:text-sm focus:outline-hidden focus:border-theme-dynamic transition-colors">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Batas Waktu (Deadline)</label>
                            <input type="date" name="due_date" value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-hidden focus:border-theme-dynamic transition-colors cursor-pointer">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Status</label>
                            <select name="status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-hidden focus:border-theme-dynamic transition-colors cursor-pointer">
                                <option value="Belum Dikerjakan" selected>Belum Dikerjakan</option>
                                <option value="Sedang Dikerjakan">Sedang Dikerjakan</option>
                                <option value="Selesai">Selesai</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Tingkat Prioritas</label>
                            <select name="priority" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-hidden focus:border-theme-dynamic transition-colors cursor-pointer">
                                <option value="Tinggi">🔴 Tinggi</option>
                                <option value="Sedang" selected>🟡 Sedang</option>
                                <option value="Rendah">🟢 Rendah</option>
                            </select>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Deskripsi Agenda</label>
                        <textarea name="description" rows="3" placeholder="Rangkuman ruang lingkup dan target agenda..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 text-xs sm:text-sm focus:outline-hidden focus:border-theme-dynamic transition-colors"></textarea>
                    </div>
                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="isNewAgendaModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold text-xs sm:text-sm transition-colors" x-text="t('batal')">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-theme-dynamic hover-bg-theme-dynamic text-white font-semibold text-xs sm:text-sm shadow-sm transition-all active:scale-95">Simpan Agenda</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 5. Modal Lihat Detail Agenda -->
    <div x-show="isAgendaDetailModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         role="dialog" 
         aria-modal="true">
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity" 
             @click="isAgendaDetailModalOpen = false"></div>
        <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg p-6 sm:p-7 space-y-5">
                <div class="flex items-start justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="space-y-1 pr-4">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800" x-text="activeAgenda.category"></span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border"
                                  :class="{
                                      'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300': activeAgenda.priority === 'Tinggi',
                                      'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300': activeAgenda.priority === 'Sedang',
                                      'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300': activeAgenda.priority === 'Rendah'
                                  }"
                                  x-text="activeAgenda.priority"></span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mt-1 leading-snug" x-text="activeAgenda.title"></h3>
                    </div>
                    <button type="button" @click="isAgendaDetailModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="space-y-4 text-xs sm:text-sm">
                    <div class="space-y-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Deskripsi Agenda</span>
                        <p class="text-slate-600 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-800/80 p-3.5 rounded-2xl border border-slate-100 dark:border-slate-700" x-text="activeAgenda.description || 'Tidak ada deskripsi rinci.'"></p>
                    </div>

                    <div class="grid grid-cols-3 gap-3 pt-2">
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700">
                            <span class="text-[11px] text-slate-400 dark:text-slate-400 block">Status</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100 text-xs sm:text-sm mt-0.5 block" x-text="activeAgenda.status"></span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700">
                            <span class="text-[11px] text-slate-400 dark:text-slate-400 block">Batas Waktu</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100 text-xs sm:text-sm mt-0.5 block" x-text="activeAgenda.due_date || 'Belum diatur'"></span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700">
                            <span class="text-[11px] text-slate-400 dark:text-slate-400 block">Progress</span>
                            <span class="font-bold text-theme-dynamic text-xs sm:text-sm mt-0.5 block" x-text="activeAgenda.progress + '%'"></span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-between border-t border-slate-100 dark:border-slate-800">
                    <button type="button" 
                            @click="isAgendaDetailModalOpen = false; openEditModal(activeAgenda)" 
                            class="px-4 py-2 rounded-xl text-theme-dynamic bg-theme-dynamic/10 hover:bg-theme-dynamic/20 font-semibold text-xs sm:text-sm transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        <span>Edit Agenda &amp; Subtugas</span>
                    </button>
                    <button type="button" @click="isAgendaDetailModalOpen = false" class="px-5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs sm:text-sm transition-colors">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. Komponen Modal Edit & Hapus (Modular & Shared) -->
    <x-modals.edit-task />
    <x-modals.delete-task />

</x-layouts.app>
