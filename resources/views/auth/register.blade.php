<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar Akun - FlowTask | Manajemen Tugas Pribadi</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (Play CDN & Theme Customization) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js untuk Interaktivitas (Mood Switcher, Bahasa, Show/Hide Password, Loading) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        [x-cloak] { display: none !important; }

        :root {
            --color-primary: #6366f1;
            --color-primary-hover: #4f46e5;
            --color-primary-light: #818cf8;
            --color-primary-glow: rgba(99, 102, 241, 0.35);
            --color-gradient-from: #6366f1;
            --color-gradient-to: #4338ca;
        }

        [data-theme="emerald"] {
            --color-primary: #10b981;
            --color-primary-hover: #059669;
            --color-primary-light: #34d399;
            --color-primary-glow: rgba(16, 185, 129, 0.35);
            --color-gradient-from: #10b981;
            --color-gradient-to: #047857;
        }

        [data-theme="rose"] {
            --color-primary: #f43f5e;
            --color-primary-hover: #e11d48;
            --color-primary-light: #fb7185;
            --color-primary-glow: rgba(244, 63, 94, 0.35);
            --color-gradient-from: #f43f5e;
            --color-gradient-to: #be123c;
        }

        [data-theme="amber"] {
            --color-primary: #f59e0b;
            --color-primary-hover: #d97706;
            --color-primary-light: #fbbf24;
            --color-primary-glow: rgba(245, 158, 11, 0.35);
            --color-gradient-from: #f59e0b;
            --color-gradient-to: #b45309;
        }

        [data-theme="cyan"] {
            --color-primary: #06b6d4;
            --color-primary-hover: #0891b2;
            --color-primary-light: #22d3ee;
            --color-primary-glow: rgba(6, 182, 212, 0.35);
            --color-gradient-from: #06b6d4;
            --color-gradient-to: #0e7490;
        }

        .bg-theme-dynamic { background-color: var(--color-primary); }
        .text-theme-dynamic { color: var(--color-primary); }
        .border-theme-dynamic { border-color: var(--color-primary); }
        .hover-bg-theme-dynamic:hover { background-color: var(--color-primary-hover); }
    </style>
</head>
<body class="bg-slate-950 font-sans text-slate-100 antialiased min-h-screen flex flex-col items-center justify-center p-4 relative selection:bg-brand-500 selection:text-white overflow-x-hidden"
      :data-theme="theme"
      x-data="{ 
          theme: localStorage.getItem('flowtask_theme') || 'indigo',
          darkMode: (localStorage.getItem('flowtask_dark_mode') === 'true') || (!('flowtask_dark_mode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
          lang: localStorage.getItem('flowtask_lang') || 'id',
          showPassword: false,
          showConfirmPassword: false,
          loading: false,
          dict: {
              ruang_kerja: { id: 'Ruang Kerja Pribadi', en: 'Personal Workspace' },
              registrasi_akun: { id: 'Registrasi Akun Baru', en: 'New Account Registration' },
              suasana_tema: { id: 'Ubah Suasana', en: 'Mood Switcher' },
              pilih_suasana: { id: 'Pilih Suasana Hati', en: 'Choose Your Mood' },
              register_sub: { id: 'Buat akun pribadi Anda untuk mulai mengelola aktivitas, daftar tugas, dan produktivitas harian.', en: 'Create your personal account to manage activities, task lists, and daily productivity.' },
              kendala_registrasi: { id: 'Terdapat kendala pada formulir registrasi:', en: 'There were issues with your registration form:' },
              label_nama: { id: 'Nama Lengkap', en: 'Full Name' },
              label_email: { id: 'Alamat Email', en: 'Email Address' },
              label_password: { id: 'Kata Sandi', en: 'Password' },
              placeholder_password: { id: 'Minimal 8 karakter', en: 'At least 8 characters' },
              label_confirm_password: { id: 'Ulangi Sandi', en: 'Confirm Password' },
              placeholder_confirm_password: { id: 'Ulangi kata sandi', en: 'Repeat password' },
              setuju_ketentuan: { id: 'Saya menyetujui ketentuan privasi serta penggunaan aplikasi manajemen tugas FlowTask.', en: 'I agree to the privacy terms and the FlowTask task management app usage.' },
              tombol_daftar: { id: 'Daftar Akun Baru', en: 'Register New Account' },
              mendaftarkan: { id: 'Mendaftarkan Akun...', en: 'Registering Account...' },
              sudah_punya_akun: { id: 'Sudah memiliki akun?', en: 'Already have an account?' },
              masuk_di_sini: { id: 'Masuk di Sini', en: 'Sign in here' },
              hak_cipta: { id: 'Aplikasi Manajemen Tugas & Produktivitas Harian', en: 'Daily Task Management & Productivity App' }
          },
          t(key) {
              if (this.dict[key] && this.dict[key][this.lang]) {
                  return this.dict[key][this.lang];
              }
              return key;
          },
          toggleLang() {
              this.lang = this.lang === 'id' ? 'en' : 'id';
              localStorage.setItem('flowtask_lang', this.lang);
          },
          toggleDarkMode() {
              this.darkMode = !this.darkMode;
              localStorage.setItem('flowtask_dark_mode', this.darkMode);
              if (this.darkMode) {
                  document.documentElement.classList.add('dark');
              } else {
                  document.documentElement.classList.remove('dark');
              }
          },
          setTheme(val) {
              this.theme = val;
              localStorage.setItem('flowtask_theme', val);
          }
      }">

    <!-- Ambient Glowing Background Accents yang bereaksi terhadap Mood Switcher -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full blur-3xl transition-all duration-700"
             :style="'background-color: var(--color-primary); opacity: 0.2;'"></div>
        <div class="absolute top-1/2 -left-32 w-[30rem] h-[30rem] rounded-full blur-3xl transition-all duration-700"
             :style="'background-color: var(--color-gradient-to); opacity: 0.15;'"></div>
        <div class="absolute -bottom-32 right-1/4 w-96 h-96 rounded-full blur-3xl transition-all duration-700"
             :style="'background-color: var(--color-primary); opacity: 0.15;'"></div>
        <!-- Grid Pattern Overlay -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#1e293b0f_1px,transparent_1px),linear-gradient(to_bottom,#1e293b0f_1px,transparent_1px)] bg-[size:4rem_4rem]"></div>
    </div>

    <!-- Header Bar: Mood Switcher & Language Switcher -->
    <div class="w-full max-w-lg relative z-20 flex items-center justify-between mb-4 px-2">
        <!-- Brand Link -->
        <a href="/" class="flex items-center gap-2 text-slate-400 hover:text-slate-200 text-xs font-semibold transition-colors">
            <svg class="w-4 h-4 text-theme-dynamic" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>FlowTask</span>
        </a>

        <!-- Controls: Language & Mood Switcher -->
        <div class="flex items-center gap-2">
            <!-- Toggle Bahasa (ID / EN) -->
            <button type="button"
                    @click="toggleLang()" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-900/90 border border-slate-800 text-slate-300 hover:text-white text-xs font-semibold hover:border-slate-700 transition-all shadow-inner active:scale-95"
                    title="Ganti Bahasa (Indonesia / English)">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                </svg>
                <span x-text="lang.toUpperCase()">ID</span>
            </button>

            <!-- Toggle Mode Gelap / Terang -->
            <button type="button"
                    @click="toggleDarkMode()" 
                    class="inline-flex items-center justify-center p-1.5 rounded-full bg-slate-900/90 border border-slate-800 text-slate-300 hover:text-white hover:border-slate-700 transition-all shadow-inner active:scale-95"
                    :title="darkMode ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'">
                <svg x-show="!darkMode" class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
                <svg x-show="darkMode" x-cloak class="w-3.5 h-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </button>

            <!-- Mood Switcher Dropdown -->
            <div class="relative" x-data="{ moodOpen: false }">
                <button type="button"
                        @click="moodOpen = !moodOpen" 
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900/90 border border-slate-800 text-slate-300 hover:text-white text-xs font-semibold hover:border-slate-700 transition-all shadow-inner active:scale-95">
                    <span class="w-2.5 h-2.5 rounded-full bg-theme-dynamic ring-2 ring-slate-800"></span>
                    <span class="hidden sm:inline" x-text="t('suasana_tema')">Ubah Suasana</span>
                    <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="moodOpen" 
                     x-cloak 
                     @click.away="moodOpen = false"
                     class="absolute right-0 mt-2 w-48 bg-slate-900/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-slate-800 p-2 z-50 space-y-1 text-xs animate-fadeIn">
                    <div class="px-2 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider" x-text="t('pilih_suasana')">Pilih Suasana</div>
                    <button type="button" @click="setTheme('indigo'); moodOpen = false" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl hover:bg-slate-800 text-slate-300 hover:text-white font-medium transition-colors">
                        <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-indigo-500"></span>Indigo (Fokus)</span>
                        <span x-show="theme === 'indigo'" class="text-indigo-400 font-bold">✓</span>
                    </button>
                    <button type="button" @click="setTheme('emerald'); moodOpen = false" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl hover:bg-slate-800 text-slate-300 hover:text-white font-medium transition-colors">
                        <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-500"></span>Emerald (Tenang)</span>
                        <span x-show="theme === 'emerald'" class="text-emerald-400 font-bold">✓</span>
                    </button>
                    <button type="button" @click="setTheme('rose'); moodOpen = false" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl hover:bg-slate-800 text-slate-300 hover:text-white font-medium transition-colors">
                        <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-rose-500"></span>Rose (Kreatif)</span>
                        <span x-show="theme === 'rose'" class="text-rose-400 font-bold">✓</span>
                    </button>
                    <button type="button" @click="setTheme('amber'); moodOpen = false" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl hover:bg-slate-800 text-slate-300 hover:text-white font-medium transition-colors">
                        <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-500"></span>Amber (Ceria)</span>
                        <span x-show="theme === 'amber'" class="text-amber-400 font-bold">✓</span>
                    </button>
                    <button type="button" @click="setTheme('cyan'); moodOpen = false" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl hover:bg-slate-800 text-slate-300 hover:text-white font-medium transition-colors">
                        <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-cyan-500"></span>Cyan (Damai)</span>
                        <span x-show="theme === 'cyan'" class="text-cyan-400 font-bold">✓</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="w-full max-w-lg relative z-10 mb-8">

        <!-- Logo & Header Identitas Proyek -->
        <div class="text-center mb-8 space-y-3">
            <!-- Brand Badge -->
            <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-slate-900/90 border border-slate-800/80 shadow-inner backdrop-blur-md mb-2">
                <span class="w-2 h-2 rounded-full bg-theme-dynamic animate-pulse"></span>
                <span class="text-xs font-semibold tracking-wide text-slate-300" x-text="t('ruang_kerja')">Ruang Kerja Pribadi</span>
                <span class="text-slate-600">•</span>
                <span class="text-xs font-bold text-theme-dynamic" x-text="t('registrasi_akun')">Registrasi Akun Baru</span>
            </div>

            <div class="flex items-center justify-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-lg transition-all duration-300"
                     :style="'background: linear-gradient(135deg, var(--color-gradient-from), var(--color-gradient-to)); box-shadow: 0 10px 25px -5px var(--color-primary-glow);'">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">Flow<span class="text-theme-dynamic">Task</span></h1>
            </div>

            <p class="text-xs sm:text-sm text-slate-400 max-w-sm mx-auto" x-text="t('register_sub')">
                Buat akun pribadi Anda untuk mulai mengelola aktivitas, daftar tugas, dan produktivitas harian.
            </p>
        </div>

        <!-- Glassmorphic Auth Card -->
        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-800/90 rounded-3xl p-6 sm:p-8 shadow-2xl shadow-black/60 relative">

            <!-- Notifikasi Error Validasi -->
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-950/60 border border-rose-800/60 text-rose-200 text-xs sm:text-sm space-y-1.5 animate-fadeIn">
                    <div class="flex items-center gap-2 font-bold text-rose-300">
                        <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span x-text="t('kendala_registrasi')">Terdapat kendala pada formulir registrasi:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-rose-300/90 pl-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form Register -->
            <form action="{{ route('register') }}" 
                  method="POST" 
                  @submit="loading = true"
                  class="space-y-4 sm:space-y-5">
                @csrf

                <!-- Input Nama Lengkap -->
                <div class="space-y-1.5">
                    <label for="name" class="block text-xs sm:text-sm font-semibold text-slate-300">
                        <span x-text="t('label_nama')">Nama Lengkap</span> <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <!-- User Icon -->
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text" 
                                id="name" 
                                name="name" 
                                value="{{ old('name') }}" 
                                required 
                                autofocus 
                                placeholder="Contoh: Nabila Putri"
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-950/70 border @error('name') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs sm:text-sm text-slate-100 placeholder-slate-500 focus:outline-hidden focus:border-theme-dynamic transition-all duration-200">
                    </div>
                    @error('name')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Input Email -->
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs sm:text-sm font-semibold text-slate-300">
                        <span x-text="t('label_email')">Alamat Email</span> <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <!-- Mail Icon -->
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input type="email" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required 
                                placeholder="nama@email.com"
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-950/70 border @error('email') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs sm:text-sm text-slate-100 placeholder-slate-500 focus:outline-hidden focus:border-theme-dynamic transition-all duration-200">
                    </div>
                    @error('email')
                        <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Grid Kata Sandi & Konfirmasi Kata Sandi -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    
                    <!-- Kata Sandi -->
                    <div class="space-y-1.5">
                        <label for="password" class="block text-xs sm:text-sm font-semibold text-slate-300">
                            <span x-text="t('label_password')">Kata Sandi</span> <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input :type="showPassword ? 'text' : 'password'" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   placeholder="Minimal 8 karakter"
                                   class="w-full pl-10 pr-9 py-2.5 bg-slate-950/70 border @error('password') border-rose-500 @else border-slate-800 @enderror rounded-xl text-xs sm:text-sm text-slate-100 placeholder-slate-500 focus:outline-hidden focus:border-theme-dynamic transition-all duration-200">
                            
                            <button type="button" 
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-slate-300 focus:outline-hidden transition-colors">
                                <svg x-show="!showPassword" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg x-show="showPassword" x-cloak class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-[11px] text-rose-400 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Konfirmasi Kata Sandi -->
                    <div class="space-y-1.5">
                        <label for="password_confirmation" class="block text-xs sm:text-sm font-semibold text-slate-300">
                            <span x-text="t('label_confirm_password')">Ulangi Sandi</span> <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <input :type="showConfirmPassword ? 'text' : 'password'" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required 
                                   placeholder="Ulangi kata sandi"
                                   class="w-full pl-10 pr-9 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-xs sm:text-sm text-slate-100 placeholder-slate-500 focus:outline-hidden focus:border-theme-dynamic transition-all duration-200">
                            
                            <button type="button" 
                                    @click="showConfirmPassword = !showConfirmPassword"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-slate-300 focus:outline-hidden transition-colors">
                                <svg x-show="!showConfirmPassword" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg x-show="showConfirmPassword" x-cloak class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Terms & Agreement Checkbox -->
                <div class="pt-1">
                    <label class="flex items-start gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" 
                                name="terms" 
                                required
                                class="w-4 h-4 mt-0.5 rounded-md bg-slate-950 border-slate-800 text-theme-dynamic focus:ring-0 focus:ring-offset-0 cursor-pointer shrink-0">
                        <span class="text-xs text-slate-400 leading-relaxed" x-text="t('setuju_ketentuan')">
                            Saya menyetujui ketentuan privasi serta penggunaan aplikasi manajemen tugas FlowTask.
                        </span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        :disabled="loading"
                        class="w-full py-3 px-4 rounded-xl text-white font-bold text-xs sm:text-sm tracking-wide shadow-lg transition-all duration-300 active:scale-[0.98] flex items-center justify-center gap-2 group mt-2"
                        :style="'background: linear-gradient(135deg, var(--color-gradient-from), var(--color-gradient-to)); box-shadow: 0 10px 25px -5px var(--color-primary-glow);'">
                    <span x-show="!loading" class="flex items-center gap-2">
                        <span x-text="t('tombol_daftar')">Daftar Akun Baru</span>
                        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </span>
                    <span x-show="loading" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="t('mendaftarkan')">Mendaftarkan Akun...</span>
                    </span>
                </button>

            </form>

            <!-- Link Navigasi ke Halaman Login -->
            <div class="mt-6 pt-5 border-t border-slate-800/80 text-center text-xs text-slate-400">
                <span x-text="t('sudah_punya_akun')">Sudah memiliki akun?</span>
                <a href="{{ route('login') }}" 
                   class="font-bold text-theme-dynamic hover:underline ml-1 transition-colors underline-offset-4">
                    <span x-text="t('masuk_di_sini')">Masuk di Sini</span>
                </a>
            </div>

        </div>

        <!-- Footer Copyright & Info -->
        <p class="text-center text-[11px] text-slate-600 mt-8">
            &copy; 2026 FlowTask • <span x-text="t('hak_cipta')">Aplikasi Manajemen Tugas &amp; Produktivitas Harian</span>
        </p>

    </div>

</body>
</html>
