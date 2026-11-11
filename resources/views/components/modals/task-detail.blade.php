<!-- ========================================================================================= -->
<!-- KOMPONEN MODAL: Lihat Rincian Tugas (Read Detail View)                                      -->
<!-- ========================================================================================= -->
<!-- Modal ini menampilkan detail lengkap tugas yang dipilih, mencakup kategori, prioritas,      -->
<!-- deskripsi, status, tenggat waktu, dan persentase progres subtugas.                          -->
<!-- ========================================================================================= -->

<div x-show="isDetailModalOpen" 
     x-cloak 
     class="fixed inset-0 z-50 overflow-y-auto" 
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true">
    
    <!-- Backdrop Blur -->
    <div x-show="isDetailModalOpen" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity" 
         @click="isDetailModalOpen = false"></div>

    <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
        <div x-show="isDetailModalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg p-6 sm:p-7 space-y-5">
            
            <!-- 1. Header Detail -->
            <div class="flex items-start justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="space-y-1 pr-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800" x-text="activeTask.category">Kategori</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800" x-text="activeTask.priority">Prioritas</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mt-1 leading-snug" x-text="activeTask.title">Judul Tugas</h3>
                </div>
                <button type="button" @click="isDetailModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- 2. Informasi Detail Tugas -->
            <div class="space-y-4 text-xs sm:text-sm">
                <div class="space-y-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Deskripsi Tugas</span>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-800/60 p-3.5 rounded-2xl border border-slate-100 dark:border-slate-700/60" x-text="activeTask.description || 'Tidak ada deskripsi rinci untuk tugas ini.'">
                        Deskripsi rinci tugas...
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700">
                        <span class="text-[11px] text-slate-400 dark:text-slate-400 block">Status Papan</span>
                        <span class="font-bold text-slate-800 dark:text-slate-100 text-xs sm:text-sm mt-0.5 block" x-text="activeTask.status">Belum Dikerjakan</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700">
                        <span class="text-[11px] text-slate-400 dark:text-slate-400 block">Tenggat Waktu</span>
                        <span class="font-bold text-slate-800 dark:text-slate-100 text-xs sm:text-sm mt-0.5 block" x-text="activeTask.dueDate || 'Belum diatur'">12 Nov 2026</span>
                    </div>
                </div>
            </div>

            <!-- 3. Footer Tombol Aksi -->
            <div class="pt-4 flex items-center justify-between border-t border-slate-100 dark:border-slate-800">
                <button type="button" 
                        @click="isDetailModalOpen = false; openEditModal(activeTask)" 
                        class="px-4 py-2 rounded-xl text-theme-dynamic bg-theme-dynamic/10 hover:bg-theme-dynamic/20 font-semibold text-xs sm:text-sm transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    <span>Edit Tugas &amp; Subtugas</span>
                </button>
                <button type="button" @click="isDetailModalOpen = false" class="px-5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs sm:text-sm transition-colors">Tutup</button>
            </div>
        </div>
    </div>
</div>
