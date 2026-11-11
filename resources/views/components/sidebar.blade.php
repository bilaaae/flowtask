@props(['active' => 'dashboard'])

@php
    // Menghitung statistik dinamis dari database MySQL untuk ringkasan di sidebar
    $totalTasksCount = \App\Models\Task::where('user_id', Auth::id())->count();
    $completedTasksCount = \App\Models\Task::where('user_id', Auth::id())->where('status', 'Selesai')->count();
    $sidebarProgress = $totalTasksCount > 0 ? (int) round(($completedTasksCount / $totalTasksCount) * 100) : 0;
@endphp

<!-- Overlay Latar Belakang Gelap untuk Mobile saat Sidebar Terbuka -->
<div x-show="sidebarOpen" 
     x-cloak
     @click="sidebarOpen = false" 
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-slate-950/60 z-40 lg:hidden backdrop-blur-xs"></div>

<!-- Sidebar Container -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       class="fixed lg:static inset-y-0 left-0 z-50 w-72 bg-white dark:bg-slate-900 border-r border-slate-200/90 dark:border-slate-800 flex flex-col justify-between transition-all duration-300 ease-in-out shrink-0 select-none shadow-xl lg:shadow-none">

    <!-- Bagian Atas Sidebar -->
    <div class="p-6 space-y-6 overflow-y-auto">
        
        <!-- Logo & Header Aplikasi -->
        <div class="flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-theme-dynamic flex items-center justify-center text-white shadow-md shadow-theme-glow transition-transform group-hover:scale-105">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <div>
                    <span class="text-xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-1.5">
                        FlowTask
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold border border-slate-200 dark:border-slate-700">v1.2</span>
                    </span>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Daily Task Management</p>
                </div>
            </a>

            <!-- Tombol Tutup Sidebar di Tampilan Mobile -->
            <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Identitas Ruang Kerja Pribadi -->
        <div class="p-4 rounded-2xl bg-gradient-to-b from-slate-50 to-slate-100/50 dark:from-slate-800/60 dark:to-slate-900/60 border border-slate-200/80 dark:border-slate-700/80 shadow-2xs space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold tracking-wider uppercase text-slate-500 dark:text-slate-400" x-text="t('ruang_kerja')">Ruang Kerja Pribadi</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-200/80 dark:bg-slate-700/80 text-slate-800 dark:text-slate-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-theme-dynamic animate-pulse"></span>
                    Active
                </span>
            </div>

            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center font-bold text-sm text-slate-800 dark:text-slate-200 shadow-2xs">
                    FT
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate" x-text="t('ruang_kerja')">Ruang Kerja Pribadi</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate" x-text="t('produktivitas')">Produktivitas & Rencana Harian</p>
                </div>
            </div>
        </div>

        <!-- Menu Navigasi Utama -->
        <nav class="space-y-1.5">
            <div class="px-2 pb-1.5 text-[11px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500" x-text="t('menu_utama')">Menu Utama</div>
            
            <!-- 1. Papan Kanban -->
            <a href="{{ route('dashboard') }}" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ $active === 'dashboard' ? 'bg-theme-dynamic text-white font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 {{ $active === 'dashboard' ? 'text-white' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                    </svg>
                    <span x-text="t('papan_kanban')">Papan Kanban</span>
                </div>
                <span class="px-2 py-0.5 text-xs rounded-md {{ $active === 'dashboard' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }} font-medium">{{ $totalTasksCount }}</span>
            </a>

            <!-- 2. Agenda Kalender -->
            <a href="{{ route('calendar') }}" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ $active === 'calendar' ? 'bg-theme-dynamic text-white font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 {{ $active === 'calendar' ? 'text-white' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span x-text="t('agenda_kalender')">Agenda Kalender</span>
                </div>
                <span class="px-2 py-0.5 text-xs rounded-md {{ $active === 'calendar' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }} font-medium">{{ $totalTasksCount }}</span>
            </a>

            <!-- 3. Daftar Agenda (Diperbarui dari Proyek Saya) -->
            <a href="{{ route('agenda') }}" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ $active === 'agenda' ? 'bg-theme-dynamic text-white font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 {{ $active === 'agenda' ? 'text-white' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span x-text="t('daftar_agenda')">Daftar Agenda</span>
                </div>
                <span class="px-2 py-0.5 text-xs rounded-md {{ $active === 'agenda' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }} font-medium">{{ $totalTasksCount }}</span>
            </a>
        </nav>

        <!-- Ringkasan Target Produktivitas Harian -->
        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-800 space-y-2.5">
            <div class="flex items-center justify-between text-xs">
                <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="t('target_hari_ini')">Target Hari Ini</span>
                <span class="font-bold text-theme-dynamic">{{ $sidebarProgress }}% Selesai</span>
            </div>
            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                <div class="bg-theme-dynamic h-2 rounded-full transition-all duration-300" style="width: {{ $sidebarProgress }}%"></div>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $completedTasksCount }} dari {{ $totalTasksCount }} tugas telah selesai.</p>
        </div>
    </div>

    <!-- Bagian Bawah Sidebar: Profil Pengguna & Logout -->
    <div class="p-4 border-t border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60">
        <div class="flex items-center justify-between p-2 rounded-xl hover:bg-white dark:hover:bg-slate-800 transition-colors border border-transparent hover:border-slate-200/70 dark:hover:border-slate-700">
            <div class="flex items-center gap-3 min-w-0">
                <div class="relative shrink-0">
                    <div class="w-10 h-10 rounded-xl bg-theme-dynamic flex items-center justify-center text-white font-bold text-sm shadow-xs">
                        {{ strtoupper(substr(Auth::user()->name ?? 'NA', 0, 2)) }}
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-emerald-500 border-2 border-white dark:border-slate-900"></span>
                </div>
                <div class="min-w-0">
                    <h5 class="text-sm font-bold text-slate-900 dark:text-white leading-tight truncate">{{ Auth::user()->name ?? 'Nabila' }}</h5>
                    <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 truncate">{{ Auth::user()->email ?? 'Personal Account' }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" :title="t('keluar')" class="p-2 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
