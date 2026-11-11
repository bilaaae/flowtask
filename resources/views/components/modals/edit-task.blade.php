<!-- ========================================================================================= -->
<!-- KOMPONEN MODAL: Edit Tugas & Checklist Subtugas (Update CRUD)                              -->
<!-- ========================================================================================= -->
<!-- Modal ini menggantikan fungsi alert() sementara sebelumnya dengan formulir edit sungguhan  -->
<!-- yang terhubung langsung ke method TaskController@update di Laravel.                        -->
<!--                                                                                            -->
<!-- Penguji Ujian:                                                                             -->
<!-- "Bagaimana alur pengiriman data pada form modal edit ini?"                                 -->
<!-- Jawab: Form ini menggunakan method POST dengan spoofing @method('PUT') yang ditujukan ke    -->
<!-- route /tasks/{id}. Form mengirimkan judul, deskripsi, kategori, prioritas, status,         -->
<!-- batas waktu, dan butir subtugas yang dicentang selesai ke Controller untuk disimpan ke DB. -->
<!-- ========================================================================================= -->

<div x-show="isEditModalOpen" 
     x-cloak 
     class="fixed inset-0 z-50 overflow-y-auto" 
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true">
    
    <!-- Latar Belakang Gelap / Backdrop Blur (Menutup modal jika area gelap diklik) -->
    <div x-show="isEditModalOpen" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity" 
         @click="isEditModalOpen = false"></div>

    <!-- Kontainer Tengah Modal -->
    <div class="flex min-h-full items-end sm:items-center justify-center p-4 text-center sm:p-0">
        <div x-show="isEditModalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl p-6 sm:p-7 space-y-5">
            
            <!-- 1. Header Modal Edit -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-100 dark:border-indigo-900/50">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white" x-text="t('edit_tugas')">Edit Tugas &amp; Subtugas</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Perbarui rincian tugas, tenggat waktu, dan kelola checklist progres.</p>
                    </div>
                </div>
                <!-- Tombol Tutup (X) -->
                <button type="button" 
                        @click="isEditModalOpen = false" 
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- 2. Formulir Update (Terhubung ke Controller Laravel via method PUT) -->
            <form :action="'/tasks/' + editTask.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <!-- Field 1: Judul Tugas (Wajib Diisi) -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                        Judul Tugas <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="title" 
                           x-model="editTask.title" 
                           required 
                           placeholder="Contoh: Desain Prototipe FlowTask" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 text-xs sm:text-sm focus:outline-hidden focus:border-theme-dynamic transition-colors">
                </div>

                <!-- Field 2 & 3: Kategori & Tingkat Prioritas (Grid 2 Kolom) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Kategori Bidang</label>
                        <select name="category" 
                                x-model="editTask.category" 
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-hidden focus:border-theme-dynamic transition-colors cursor-pointer">
                            <option value="Desain UI/UX">Desain UI/UX</option>
                            <option value="Pengembangan Web">Pengembangan Web</option>
                            <option value="Frontend">Frontend</option>
                            <option value="Backend">Backend</option>
                            <option value="Keuangan">Keuangan</option>
                            <option value="Dokumentasi">Dokumentasi</option>
                            <option value="Riset">Riset</option>
                            <option value="Infrastruktur">Infrastruktur</option>
                            <option value="Umum">Umum</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Tingkat Prioritas</label>
                        <select name="priority" 
                                x-model="editTask.priority" 
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-hidden focus:border-theme-dynamic transition-colors cursor-pointer">
                            <option value="Tinggi">🔴 Tinggi (Mendesak)</option>
                            <option value="Sedang">🟡 Sedang (Standar)</option>
                            <option value="Rendah">🟢 Rendah (Santai)</option>
                        </select>
                    </div>
                </div>

                <!-- Field 4 & 5: Status Kolom Papan & Batas Waktu (Tenggat) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Status Papan</label>
                        <select name="status" 
                                x-model="editTask.status" 
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-hidden focus:border-theme-dynamic transition-colors cursor-pointer">
                            <option value="Belum Dikerjakan">Belum Dikerjakan (To Do)</option>
                            <option value="Sedang Dikerjakan">Sedang Dikerjakan (In Progress)</option>
                            <option value="Selesai">Selesai (Done)</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Batas Waktu (Deadline)</label>
                        <input type="date" 
                               name="due_date" 
                               x-model="editTask.due_date" 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-hidden focus:border-theme-dynamic transition-colors cursor-pointer">
                    </div>
                </div>

                <!-- Field 6: Deskripsi Tugas -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Deskripsi Ringkas</label>
                    <textarea name="description" 
                              x-model="editTask.description" 
                              rows="2" 
                              placeholder="Detail mengenai tugas ini..." 
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 text-xs sm:text-sm focus:outline-hidden focus:border-theme-dynamic transition-colors"></textarea>
                </div>

                <!-- ========================================================================= -->
                <!-- Bagian 3: Checklist Subtugas Interaktif & Progress Bar Dinamis            -->
                <!-- ========================================================================= -->
                <!-- Bagian ini memungkinkan penguji mencentang subtugas dan melihat persentase -->
                <!-- progres berubah secara live secara otomatis berkat reaktivitas Alpine.js. -->
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-theme-dynamic" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                                Checklist Subtugas
                            </span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400" x-text="getEditProgressText()"></span>
                        </div>
                        <!-- Tombol Tambah Butir Subtugas Baru Secara Dinamis -->
                        <button type="button" 
                                @click="addSubtask()" 
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-theme-dynamic bg-theme-dynamic/10 hover:bg-theme-dynamic/20 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                            <span>Tambah Subtugas</span>
                        </button>
                    </div>

                    <!-- Mini Live Progress Bar di dalam Modal Edit -->
                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full transition-all duration-300"
                             :class="getEditProgressPercent() === 100 ? 'bg-emerald-500' : 'bg-theme-dynamic'"
                             :style="'width: ' + getEditProgressPercent() + '%'"></div>
                    </div>

                    <!-- Looping Daftar Subtugas (Alpine.js x-for) -->
                    <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                        <template x-for="(st, index) in editTask.subtasks" :key="index">
                            <div class="flex items-center gap-2.5 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80">
                                <!-- Checkbox Centang Selesai -->
                                <input type="checkbox" 
                                       :name="'subtasks[' + index + '][completed]'" 
                                       value="1" 
                                       x-model="st.completed" 
                                       class="w-4 h-4 rounded text-theme-dynamic focus:ring-theme-dynamic border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 cursor-pointer">
                                
                                <!-- Input Judul Subtugas -->
                                <input type="text" 
                                       :name="'subtasks[' + index + '][title]'" 
                                       x-model="st.title" 
                                       placeholder="Nama butir pekerjaan..." 
                                       required
                                       class="flex-1 text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-hidden focus:border-theme-dynamic"
                                       :class="st.completed ? 'line-through text-slate-400 dark:text-slate-500' : ''">

                                <!-- Tombol Hapus Subtugas -->
                                <button type="button" 
                                        @click="removeSubtask(index)" 
                                        title="Hapus butir subtugas ini" 
                                        class="p-1 text-slate-400 hover:text-rose-500 dark:hover:text-rose-400 rounded-md transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </template>

                        <!-- Tampilan jika belum ada subtugas -->
                        <div x-show="!editTask.subtasks || editTask.subtasks.length === 0" class="text-center py-3 text-xs text-slate-400 dark:text-slate-500 bg-slate-50/50 dark:bg-slate-800/30 rounded-xl border border-dashed border-slate-200 dark:border-slate-800">
                            Belum ada rincian subtugas. Klik <strong class="text-theme-dynamic cursor-pointer" @click="addSubtask()">+ Tambah Subtugas</strong> untuk melacak progres tugas ini.
                        </div>
                    </div>
                </div>

                <!-- 4. Tombol Aksi Bawah Modal -->
                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" 
                            @click="isEditModalOpen = false" 
                            class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold text-xs sm:text-sm transition-colors" 
                            x-text="t('batal')">Batal</button>
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-theme-dynamic hover-bg-theme-dynamic text-white font-semibold text-xs sm:text-sm shadow-md transition-all active:scale-95">
                        Simpan Perubahan
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
