<!-- ========================================================================================= -->
<!-- KOMPONEN MODAL: Konfirmasi Hapus Tugas (Delete CRUD)                                       -->
<!-- ========================================================================================= -->
<!-- Modal ini mengonfirmasi tindakan penghapusan tugas dan mengirimkan HTTP DELETE ke          -->
<!-- rute /tasks/{id} (TaskController@destroy) untuk menghapus record dari database MySQL.       -->
<!--                                                                                            -->
<!-- Penguji Ujian:                                                                             -->
<!-- "Mengapa operasi hapus data menggunakan method DELETE dan bukan GET?"                      -->
<!-- Jawab: Berdasarkan standar arsitektur RESTful API, operasi yang mengubah atau menghapus    -->
<!-- state di server tidak boleh menggunakan GET (karena GET bersifat idempotent/safe dan dapat -->
<!-- di-cache atau terpicu tanpa sengaja). Laravel menggunakan @method('DELETE') dengan token   -->
<!-- @csrf agar operasi hapus aman dan terlindungi dari manipulasi permintaan palsu.            -->
<!-- ========================================================================================= -->

<div x-show="isDeleteModalOpen" 
     x-cloak 
     class="fixed inset-0 z-50 overflow-y-auto" 
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true">
    
    <!-- Backdrop Blur Gelap -->
    <div x-show="isDeleteModalOpen" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity" 
         @click="isDeleteModalOpen = false"></div>

    <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
        <div x-show="isDeleteModalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 sm:p-7 space-y-4">
            
            <!-- Ikon Peringatan Merah -->
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 border border-rose-100 dark:border-rose-900/60">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white" x-text="t('hapus_tugas')">Hapus Tugas</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tindakan ini permanen dan menghapus data dari database.</p>
                </div>
            </div>

            <!-- Pesan Konfirmasi -->
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                Apakah Anda yakin ingin menghapus tugas <strong class="text-slate-900 dark:text-white font-semibold" x-text="activeTask.title">ini</strong> dari papan alur kerja Anda?
            </p>

            <!-- Form Eksekusi Delete (Terhubung ke Controller TaskController@destroy) -->
            <form :action="'/tasks/' + activeTask.id" method="POST">
                @csrf
                @method('DELETE')

                <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" 
                            @click="isDeleteModalOpen = false" 
                            class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold text-xs sm:text-sm transition-colors" 
                            x-text="t('batal')">Batal</button>
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs sm:text-sm shadow-sm transition-all active:scale-95" 
                            x-text="t('hapus_tugas')">Ya, Hapus</button>
                </div>
            </form>

        </div>
    </div>
</div>
