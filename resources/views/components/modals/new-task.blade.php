<!-- ========================================================================================= -->
<!-- KOMPONEN MODAL: Tambah Tugas Baru (Create CRUD)                                             -->
<!-- ========================================================================================= -->
<!-- Modal formulir interaktif untuk membuat tugas baru dan menyimpannya ke tabel 'tasks' MySQL -->
<!-- melalui rute POST /tasks (TaskController@store).                                           -->
<!--                                                                                            -->
<!-- Penguji Ujian:                                                                             -->
<!-- "Bagaimana proses penyimpanan data tugas baru di FlowTask?"                                -->
<!-- Jawab: Data dari form ini dikirimkan via metode HTTP POST ke endpoint tasks.store.       -->
<!-- Request membawa token @csrf untuk proteksi keamanan dari serangan Cross-Site Request       -->
<!-- Forgery, lalu divalidasi oleh TaskController sebelum disimpan ke database MySQL.           -->
<!-- ========================================================================================= -->

<div x-show="isNewTaskModalOpen" 
     x-cloak 
     class="fixed inset-0 z-50 overflow-y-auto" 
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true">
    
    <!-- Backdrop Blur Gelap -->
    <div x-show="isNewTaskModalOpen" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity" 
         @click="isNewTaskModalOpen = false"></div>

    <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
        <div x-show="isNewTaskModalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg p-6 sm:p-7 space-y-5">
            
            <!-- 1. Header Modal Tambah Tugas -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-theme-dynamic/10 dark:bg-theme-dynamic/20 text-theme-dynamic flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white" x-text="t('tambah_tugas')">Tambah Tugas Baru</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Buat target dan tugas baru untuk alur kerja Anda.</p>
                    </div>
                </div>
                <!-- Tombol Close Modal (X) -->
                <button type="button" @click="isNewTaskModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- 2. Form Input Tugas Baru (Terhubung ke Controller Laravel via route tasks.store) -->
            <form action="{{ route('tasks.store') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Input Judul Tugas -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                        Judul Tugas <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="title" 
                           required 
                           placeholder="Contoh: Menyusun Laporan Keuangan Q4" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs sm:text-sm focus:outline-hidden focus:border-theme-dynamic transition-colors">
                </div>

                <!-- Kategori & Prioritas Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Kategori Bidang</label>
                        <select name="category" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-hidden focus:border-theme-dynamic transition-colors cursor-pointer">
                            <option value="Desain UI/UX">Desain UI/UX</option>
                            <option value="Pengembangan Web" selected>Pengembangan Web</option>
                            <option value="Frontend">Frontend</option>
                            <option value="Backend">Backend</option>
                            <option value="Keuangan">Keuangan</option>
                            <option value="Dokumentasi">Dokumentasi</option>
                            <option value="Riset">Riset</option>
                            <option value="Infrastruktur">Infrastruktur</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Tingkat Prioritas</label>
                        <select name="priority" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-hidden focus:border-theme-dynamic transition-colors cursor-pointer">
                            <option value="Tinggi">🔴 Tinggi (Mendesak)</option>
                            <option value="Sedang" selected>🟡 Sedang (Standar)</option>
                            <option value="Rendah">🟢 Rendah (Santai)</option>
                        </select>
                    </div>
                </div>

                <!-- Status & Tenggat Waktu -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Kolom Status</label>
                        <select name="status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-hidden focus:border-theme-dynamic transition-colors cursor-pointer">
                            <option value="Belum Dikerjakan" selected>Belum Dikerjakan</option>
                            <option value="Sedang Dikerjakan">Sedang Dikerjakan</option>
                            <option value="Selesai">Selesai</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Batas Waktu (Deadline)</label>
                        <input type="date" 
                               name="due_date" 
                               value="{{ date('Y-m-d') }}" 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-hidden focus:border-theme-dynamic transition-colors cursor-pointer">
                    </div>
                </div>

                <!-- Deskripsi Tugas -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Deskripsi Ringkas</label>
                    <textarea name="description" 
                              rows="3" 
                              placeholder="Jelaskan detail singkat tentang tugas ini..." 
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs sm:text-sm focus:outline-hidden focus:border-theme-dynamic transition-colors"></textarea>
                </div>

                <!-- Tombol Aksi Form -->
                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" 
                            @click="isNewTaskModalOpen = false" 
                            class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold text-xs sm:text-sm transition-colors" 
                            x-text="t('batal')">Batal</button>
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-theme-dynamic hover-bg-theme-dynamic text-white font-semibold text-xs sm:text-sm shadow-sm transition-all active:scale-95" 
                            x-text="t('simpan')">Simpan Tugas</button>
                </div>
            </form>

        </div>
    </div>
</div>
