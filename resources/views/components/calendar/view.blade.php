@props(['tasks' => []])

<div x-data="{
    currentDate: new Date(),
    currentYear: (new Date()).getFullYear(),
    currentMonth: (new Date()).getMonth(),
    selectedDateKey: '',
    selectedDateFormatted: '',
    calendarDays: [],
    monthNamesId: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
    monthNamesEn: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    dayNamesId: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
    dayNamesEn: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
    
    // Data tugas dari database MySQL
    serverTasks: {{ Js::from($tasks) }},
    
    // Data jadwal & agenda terintegrasi
    scheduledEvents: [
        {
            title: 'Pengujian Validasi Form & Request Handling',
            category: 'Testing & QA',
            priority: 'Tinggi',
            status: 'Selesai',
            description: 'Menjalankan pengujian error handling formulir dan pencegahan duplikasi data.',
            dateOffset: 0 // Hari ini
        },
        {
            title: 'Implementasi Blade Template & Tailwind CSS',
            category: 'Frontend Dev',
            priority: 'Sedang',
            status: 'Sedang Berjalan',
            description: 'Membangun arsitektur antarmuka Kanban modular, mood switcher, dan kalender interaktif.',
            dateOffset: 1 // Besok
        },
        {
            title: 'Desain Prototipe Visual FlowTask',
            category: 'Desain UI/UX',
            priority: 'Tinggi',
            status: 'Belum Dikerjakan',
            description: 'Merancang tata letak dan palet warna antarmuka kanban modern untuk alur kerja pengguna.',
            dateOffset: 2
        },
        {
            title: 'Integrasi Sistem Autentikasi',
            category: 'Keamanan',
            priority: 'Tinggi',
            status: 'Sedang Berjalan',
            description: 'Memverifikasi kredensial login via Auth::attempt, regenerasi session, dan pengamanan kata sandi.',
            dateOffset: 4
        },
        {
            title: 'Konfigurasi Skema Relasi Database',
            category: 'Backend',
            priority: 'Rendah',
            status: 'Belum Dikerjakan',
            description: 'Menyiapkan struktur migrasi tabel tasks, relasi user_id, indexation, dan validasi data Eloquent.',
            dateOffset: 7
        },
        {
            title: 'Penyusunan Anggaran & Keuangan Operasional',
            category: 'Keuangan',
            priority: 'Sedang',
            status: 'Perencanaan',
            description: 'Merekapitulasi alokasi pengeluaran, proyeksi pendapatan, dan efisiensi belanja operasional.',
            dateOffset: 14
        },
        {
            title: 'Dokumentasi Sistem & Pedoman Kerja',
            category: 'Dokumentasi',
            priority: 'Rendah',
            status: 'Perencanaan',
            description: 'Menyusun pedoman arsitektur clean-code, panduan rilis fitur baru, dan checklist audit.',
            dateOffset: 20
        }
    ],

    // Menghasilkan format kunci tanggal YYYY-MM-DD
    formatDateKey(d) {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    },

    // Format tampilan tanggal untuk dibaca manusia
    formatDateHuman(d) {
        const day = d.getDate();
        const monthNames = (this.lang === 'en') ? this.monthNamesEn : this.monthNamesId;
        const month = monthNames[d.getMonth()];
        const year = d.getFullYear();
        return `${day} ${month} ${year}`;
    },

    get currentMonthTitle() {
        const monthNames = (this.lang === 'en') ? this.monthNamesEn : this.monthNamesId;
        return `${monthNames[this.currentMonth]} ${this.currentYear}`;
    },

    get activeDayEvents() {
        return this.scheduledEvents.filter(ev => ev.computedDateKey === this.selectedDateKey);
    },

    // Pindah ke Bulan Sebelumnya
    prevMonth() {
        if (this.currentMonth === 0) {
            this.currentMonth = 11;
            this.currentYear--;
        } else {
            this.currentMonth--;
        }
        this.generateDays();
    },

    // Pindah ke Bulan Berikutnya
    nextMonth() {
        if (this.currentMonth === 11) {
            this.currentMonth = 0;
            this.currentYear++;
        } else {
            this.currentMonth++;
        }
        this.generateDays();
    },

    // Kembali ke Hari Ini
    goToToday() {
        const now = new Date();
        this.currentMonth = now.getMonth();
        this.currentYear = now.getFullYear();
        this.selectDateByObj({
            day: now.getDate(),
            month: now.getMonth(),
            year: now.getFullYear(),
            dateKey: this.formatDateKey(now)
        });
        this.generateDays();
    },

    // Pemilihan Tanggal Interaktif
    selectDateByObj(dayObj) {
        this.selectedDateKey = dayObj.dateKey;
        const d = new Date(dayObj.year, dayObj.month, dayObj.day);
        this.selectedDateFormatted = this.formatDateHuman(d);
        this.generateDays();
    },

    // Inisialisasi & Generate Grid Penanggalan Bulanan
    initCalendar() {
        const today = new Date();
        this.selectedDateKey = this.formatDateKey(today);
        this.selectedDateFormatted = this.formatDateHuman(today);

        // Jika terdapat data tasks dari database MySQL, tampilkan di kalender
        if (this.serverTasks && this.serverTasks.length > 0) {
            this.scheduledEvents = this.serverTasks.map(t => {
                const rawDate = t.due_date ? (t.due_date.includes('T') ? t.due_date.split('T')[0] : t.due_date) : '';
                return {
                    id: t.id,
                    title: t.title,
                    category: t.category || 'Pekerjaan',
                    priority: t.priority || 'Sedang',
                    status: t.status || 'Belum Dikerjakan',
                    description: t.description || '',
                    computedDateKey: rawDate,
                    subtasks: t.subtasks || []
                };
            });
        } else {
            // Hitung tanggal dinamis untuk event default berdasarkan hari ini
            this.scheduledEvents.forEach(ev => {
                const evDate = new Date();
                evDate.setDate(today.getDate() + ev.dateOffset);
                ev.computedDateKey = this.formatDateKey(evDate);
            });
        }

        this.generateDays();
    },

    generateDays() {
        const firstDayIndex = new Date(this.currentYear, this.currentMonth, 1).getDay();
        const totalDaysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
        const prevMonthLastDate = new Date(this.currentYear, this.currentMonth, 0).getDate();
        const todayKey = this.formatDateKey(new Date());

        const days = [];

        // 1. Hari-hari dari bulan sebelumnya (trailing)
        for (let i = firstDayIndex - 1; i >= 0; i--) {
            const dayNum = prevMonthLastDate - i;
            const prevMonthIndex = this.currentMonth === 0 ? 11 : this.currentMonth - 1;
            const prevYear = this.currentMonth === 0 ? this.currentYear - 1 : this.currentYear;
            const dateObj = new Date(prevYear, prevMonthIndex, dayNum);
            const dateKey = this.formatDateKey(dateObj);

            days.push({
                day: dayNum,
                month: prevMonthIndex,
                year: prevYear,
                dateKey: dateKey,
                isCurrentMonth: false,
                isToday: dateKey === todayKey,
                isSelected: dateKey === this.selectedDateKey,
                events: this.scheduledEvents.filter(e => e.computedDateKey === dateKey)
            });
        }

        // 2. Hari-hari bulan saat ini
        for (let d = 1; d <= totalDaysInMonth; d++) {
            const dateObj = new Date(this.currentYear, this.currentMonth, d);
            const dateKey = this.formatDateKey(dateObj);

            days.push({
                day: d,
                month: this.currentMonth,
                year: this.currentYear,
                dateKey: dateKey,
                isCurrentMonth: true,
                isToday: dateKey === todayKey,
                isSelected: dateKey === this.selectedDateKey,
                events: this.scheduledEvents.filter(e => e.computedDateKey === dateKey)
            });
        }

        // 3. Hari-hari bulan berikutnya (leading) untuk menggenapi kelipatan 7
        const remainingCells = (7 - (days.length % 7)) % 7;
        const totalNeeded = (days.length + remainingCells < 35) ? (35 - days.length) : remainingCells;
        
        for (let nextDay = 1; nextDay <= totalNeeded; nextDay++) {
            const nextMonthIndex = this.currentMonth === 11 ? 0 : this.currentMonth + 1;
            const nextYear = this.currentMonth === 11 ? this.currentYear + 1 : this.currentYear;
            const dateObj = new Date(nextYear, nextMonthIndex, nextDay);
            const dateKey = this.formatDateKey(dateObj);

            days.push({
                day: nextDay,
                month: nextMonthIndex,
                year: nextYear,
                dateKey: dateKey,
                isCurrentMonth: false,
                isToday: dateKey === todayKey,
                isSelected: dateKey === this.selectedDateKey,
                events: this.scheduledEvents.filter(e => e.computedDateKey === dateKey)
            });
        }

        this.calendarDays = days;
    }
}" x-init="initCalendar()">

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
        
        <!-- ===================================================================== -->
        <!-- KOLOM 1 & 2: GRID KALENDER BULANAN DINAMIS                            -->
        <!-- ===================================================================== -->
        <div class="xl:col-span-2 bg-white dark:bg-slate-900 rounded-3xl p-5 sm:p-6 border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-5">
            
            <!-- Navigasi Bulan, Tahun & Aksi Cepat -->
            <div class="flex items-center justify-between flex-wrap gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white" x-text="currentMonthTitle">November 2026</h3>
                    <button type="button" 
                            @click="goToToday()" 
                            class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        Hari Ini
                    </button>
                </div>

                <!-- Tombol Navigasi Panah (Bulan Lalu & Bulan Depan) -->
                <div class="flex items-center gap-1.5">
                    <button type="button" 
                            @click="prevMonth()" 
                            class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 transition-colors"
                            title="Bulan Sebelumnya">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                    <button type="button" 
                            @click="nextMonth()" 
                            class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 transition-colors"
                            title="Bulan Berikutnya">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>
            </div>

            <!-- Baris Nama Hari -->
            <div class="grid grid-cols-7 gap-2 text-center text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                <template x-for="dayName in (lang === 'en' ? dayNamesEn : dayNamesId)" :key="dayName">
                    <div x-text="dayName"></div>
                </template>
            </div>

            <!-- Grid Tanggal Dinamis (Rendered dari JavaScript Alpine) -->
            <div class="grid grid-cols-7 gap-2 text-xs">
                <template x-for="(cell, index) in calendarDays" :key="index">
                    <div @click="selectDateByObj(cell)" 
                         class="p-2 sm:p-2.5 rounded-2xl min-h-[5.5rem] flex flex-col justify-between cursor-pointer transition-all duration-150 border"
                         :class="{
                             'bg-slate-50/50 dark:bg-slate-900/40 text-slate-300 dark:text-slate-600 border-transparent': !cell.isCurrentMonth,
                             'bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 border-slate-100 dark:border-slate-700/80 text-slate-700 dark:text-slate-200': cell.isCurrentMonth && !cell.isSelected,
                             'bg-indigo-50/60 dark:bg-indigo-950/40 border-2 border-theme-dynamic shadow-xs': cell.isSelected
                         }">
                        
                        <!-- Angka Tanggal & Indikator Hari Ini -->
                        <div class="flex items-center justify-between">
                            <span :class="{
                                      'w-6 h-6 rounded-full bg-theme-dynamic text-white font-bold flex items-center justify-center text-xs shadow-xs': cell.isToday,
                                      'font-bold text-slate-900 dark:text-white': cell.isCurrentMonth && !cell.isToday,
                                      'font-medium text-slate-400 dark:text-slate-600': !cell.isCurrentMonth
                                  }" 
                                  x-text="cell.day"></span>
                            
                            <!-- Dot penanda ada event/tugas pada tanggal ini -->
                            <span x-show="cell.events && cell.events.length > 0" 
                                  class="w-2 h-2 rounded-full bg-theme-dynamic" 
                                  :class="cell.isSelected ? 'animate-pulse' : ''"></span>
                        </div>

                        <!-- Pill ringkasan event jika ada pada tanggal tersebut -->
                        <div x-show="cell.events && cell.events.length > 0" class="space-y-1 mt-1">
                            <template x-for="(ev, evIdx) in cell.events.slice(0, 1)" :key="evIdx">
                                <div class="px-1.5 py-0.5 rounded text-[10px] font-semibold truncate"
                                     :class="{
                                         'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300': ev.status === 'Selesai',
                                         'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300': ev.status === 'Sedang Berjalan',
                                         'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300': ev.status === 'Belum Dikerjakan',
                                         'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300': ev.status === 'Perencanaan'
                                     }"
                                     x-text="ev.title"></div>
                            </template>
                            <span x-show="cell.events && cell.events.length > 1" 
                                  class="text-[9px] font-bold text-slate-400 block" 
                                  x-text="'+' + (cell.events.length - 1) + ' lagi'"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- KOLOM 3: PANEL AGENDA TANGGAL TERPILIH (UPDATE OTOMATIS)              -->
        <!-- ===================================================================== -->
        <div class="space-y-6">

            <!-- Kartu Agenda Tanggal Terpilih -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h4 class="font-bold text-slate-900 dark:text-white text-sm">Agenda Tanggal Terpilih</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400" x-text="selectedDateFormatted"></p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-theme-dynamic text-white">
                        <span x-text="activeDayEvents.length + ' Agenda'"></span>
                    </span>
                </div>

                <!-- Daftar Event pada Tanggal Terpilih -->
                <div class="space-y-3">
                    <template x-for="(event, idx) in activeDayEvents" :key="idx">
                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 space-y-2 animate-fadeIn">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold"
                                      :class="{
                                          'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300': event.status === 'Selesai',
                                          'bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300': event.status === 'Sedang Berjalan',
                                          'bg-purple-100 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300': event.status === 'Belum Dikerjakan',
                                          'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300': event.status === 'Perencanaan'
                                      }" 
                                      x-text="event.category"></span>
                                
                                <span class="text-[11px] font-semibold"
                                      :class="{
                                          'text-emerald-600 dark:text-emerald-400': event.status === 'Selesai',
                                          'text-blue-600 dark:text-blue-400': event.status === 'Sedang Berjalan',
                                          'text-amber-600 dark:text-amber-400': event.status === 'Belum Dikerjakan' || event.status === 'Perencanaan'
                                      }"
                                      x-text="event.status"></span>
                            </div>
                            <h5 class="text-sm font-bold text-slate-900 dark:text-white leading-snug" x-text="event.title"></h5>
                            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed" x-text="event.description"></p>
                        </div>
                    </template>

                    <!-- Kondisi saat Tidak Ada Agenda pada Tanggal yang Dipilih -->
                    <div x-show="activeDayEvents.length === 0" class="py-8 text-center space-y-2">
                        <div class="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mx-auto">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">Tidak ada agenda pada tanggal ini</p>
                        <p class="text-[11px] text-slate-400 max-w-xs mx-auto">Klik tombol "+ Tambah Agenda" di atas untuk menambahkan target baru pada tanggal ini.</p>
                    </div>
                </div>
            </div>

            <!-- Catatan Ringkas Produktivitas -->
            <div class="p-5 rounded-3xl bg-gradient-to-br from-slate-900 to-slate-800 text-white shadow-xl space-y-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-theme-dynamic animate-pulse"></span>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-300">Tips Kalender FlowTask</h4>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Navigasikan kalender untuk melihat jadwal lintas bulan dan klik pada tanggal mana saja untuk memeriksa agenda yang telah dijadwalkan secara seketika.
                </p>
            </div>

        </div>

    </div>

</div>
