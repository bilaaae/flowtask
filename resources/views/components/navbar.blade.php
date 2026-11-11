@props([
    'title' => 'Manajemen Tugas',
    'breadcrumb' => 'Papan Kanban',
    'ctaText' => 'tambah_tugas',
    'ctaAction' => 'isNewTaskModalOpen = true'
])

<header class="sticky top-0 z-30 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800 px-4 sm:px-6 lg:px-8 py-4 transition-colors duration-200">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Kiri: Mobile Toggle + Judul + Sapaan Personal -->
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-xl text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-0.5">
                    <span class="font-medium" x-text="t('ruang_kerja')">Ruang Kerja</span>
                    <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $breadcrumb }}</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $title }}</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1.5">
                    <span><span x-text="t('sapaan')">Halo,</span> <strong class="text-slate-800 dark:text-slate-200 font-semibold">{{ Auth::user()->name ?? 'Nabila' }}</strong>! 👋</span>
                    <span class="hidden sm:inline text-slate-400 dark:text-slate-600">•</span>
                    <span class="hidden sm:inline" x-text="t('sub_sapaan')">Semangat menuntaskan agenda dan target produktivitas Anda hari ini!</span>
                </p>
            </div>
        </div>

        <!-- Kanan: Language, Dark Mode, Mood Switcher & Tombol Aksi Utama -->
        <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap sm:flex-nowrap">

            <!-- 1. Toggle Ganti Bahasa Instan (ID / EN) -->
            <button type="button"
                    @click="toggleLang()" 
                    title="Ubah Bahasa / Switch Language"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition-all shadow-2xs border border-slate-200 dark:border-slate-700 active:scale-95">
                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                </svg>
                <span x-text="lang.toUpperCase()">ID</span>
            </button>

            <!-- 2. Toggle Mode Gelap / Terang (Dark / Light Mode) -->
            <button type="button"
                    @click="toggleDarkMode()" 
                    :title="darkMode ? 'Beralih ke Mode Terang (Light Mode)' : 'Beralih ke Mode Gelap (Dark Mode)'"
                    class="inline-flex items-center justify-center p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-700/80 transition-all shadow-2xs active:scale-95">
                <!-- Moon Icon (Muncul saat Light Mode untuk beralih ke Gelap) -->
                <svg x-show="!darkMode" class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
                <!-- Sun Icon (Muncul saat Dark Mode untuk beralih ke Terang) -->
                <svg x-show="darkMode" x-cloak class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </button>

            <!-- 3. Mood Switcher (Kustomisasi Tema Warna Sesuai Suasana Hati) -->
            <div class="relative" x-data="{ moodOpen: false }">
                <button type="button"
                        @click="moodOpen = !moodOpen" 
                        class="inline-flex items-center gap-2 px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-700/80 transition-all shadow-2xs active:scale-95">
                    <span class="w-3 h-3 rounded-full bg-theme-dynamic ring-2 ring-white dark:ring-slate-900"></span>
                    <span class="hidden sm:inline" x-text="t('suasana_tema')">Suasana Tema</span>
                    <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="moodOpen" 
                     x-cloak 
                     @click.away="moodOpen = false"
                     class="absolute right-0 mt-2 w-52 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 p-2.5 z-40 space-y-1 text-xs animate-fadeIn">
                    <div class="px-2 py-1 text-[11px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">Pilih Suasana Hati</div>
                    <button type="button" @click="setTheme('indigo'); moodOpen = false" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl hover:bg-indigo-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-medium transition-colors">
                        <span class="flex items-center gap-2"><span class="w-3.5 h-3.5 rounded-full bg-indigo-600"></span>Indigo (Fokus)</span>
                        <span x-show="theme === 'indigo'" class="text-indigo-600 dark:text-indigo-400 font-bold">✓</span>
                    </button>
                    <button type="button" @click="setTheme('emerald'); moodOpen = false" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl hover:bg-emerald-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-medium transition-colors">
                        <span class="flex items-center gap-2"><span class="w-3.5 h-3.5 rounded-full bg-emerald-600"></span>Emerald (Tenang)</span>
                        <span x-show="theme === 'emerald'" class="text-emerald-600 dark:text-emerald-400 font-bold">✓</span>
                    </button>
                    <button type="button" @click="setTheme('rose'); moodOpen = false" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl hover:bg-rose-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-medium transition-colors">
                        <span class="flex items-center gap-2"><span class="w-3.5 h-3.5 rounded-full bg-rose-600"></span>Rose (Kreatif)</span>
                        <span x-show="theme === 'rose'" class="text-rose-600 dark:text-rose-400 font-bold">✓</span>
                    </button>
                    <button type="button" @click="setTheme('amber'); moodOpen = false" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl hover:bg-amber-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-medium transition-colors">
                        <span class="flex items-center gap-2"><span class="w-3.5 h-3.5 rounded-full bg-amber-600"></span>Amber (Ceria)</span>
                        <span x-show="theme === 'amber'" class="text-amber-600 dark:text-amber-400 font-bold">✓</span>
                    </button>
                    <button type="button" @click="setTheme('cyan'); moodOpen = false" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl hover:bg-cyan-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-medium transition-colors">
                        <span class="flex items-center gap-2"><span class="w-3.5 h-3.5 rounded-full bg-cyan-600"></span>Cyan (Damai)</span>
                        <span x-show="theme === 'cyan'" class="text-cyan-600 dark:text-cyan-400 font-bold">✓</span>
                    </button>
                </div>
            </div>

            <!-- 4. Tombol Aksi Utama (CTA) -->
            @if ($ctaText)
                <button type="button"
                        @click="{{ $ctaAction }}" 
                        class="inline-flex items-center justify-center gap-2 px-3.5 py-2 bg-theme-dynamic hover-bg-theme-dynamic text-white text-xs sm:text-sm font-semibold rounded-xl shadow-sm transition-all duration-200 active:scale-95 shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="hidden sm:inline" x-text="t('{{ $ctaText }}')">{{ $ctaText }}</span>
                </button>
            @endif

        </div>

    </div>
</header>
