@props(['title' => 'FlowTask - Manajemen Tugas Harian'])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Anti-FOUT Script (Dark Mode & Theme Detection Early) -->
    <script>
        (function() {
            try {
                var storedDark = localStorage.getItem('flowtask_dark_mode');
                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (storedDark === 'true' || (storedDark === null && prefersDark)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {}
        })();
    </script>

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
                        theme: {
                            primary: 'var(--color-primary)',
                            hover: 'var(--color-primary-hover)',
                            light: 'var(--color-primary-light)',
                            glow: 'var(--color-primary-glow)',
                        }
                    }
                }
            }
        }
    </script>

    <!-- ========================================================================= -->
    <!-- LOGIKA APLIKASI FLOWTASK & ALPINE.JS STORE (TERISOLASI DI DALAM SCRIPT)   -->
    <!-- ========================================================================= -->
    <!-- Seluruh logika JavaScript diletakkan di dalam tag <script> agar tidak     -->
    <!-- bocor ke dalam atribut HTML atau merusak parsing tampilan antarmuka.     -->
    <script>
        function flowtaskApp() {
            return {
                theme: localStorage.getItem('flowtask_theme') || 'indigo',
                darkMode: (localStorage.getItem('flowtask_dark_mode') === 'true') || (!('flowtask_dark_mode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
                lang: localStorage.getItem('flowtask_lang') || 'id',
                sidebarOpen: false,
                isNewTaskModalOpen: false,
                isEditModalOpen: false,
                isDetailModalOpen: false,
                isDeleteModalOpen: false,
                activeDropdown: null,
                draggedTaskId: null,
                statusToastMessage: '',
                showStatusToast: false,
                searchQuery: '',
                selectedPriority: 'all',
                selectedCategory: 'all',
                selectedStatus: 'all',
                activeTask: {
                    id: null,
                    title: '',
                    category: '',
                    description: '',
                    priority: '',
                    status: '',
                    dueDate: ''
                },
                editTask: {
                    id: null,
                    title: '',
                    category: 'Pengembangan Web',
                    description: '',
                    priority: 'Sedang',
                    status: 'Belum Dikerjakan',
                    due_date: '',
                    subtasks: []
                },
                dict: {
                    ruang_kerja: { id: 'Ruang Kerja Pribadi', en: 'Personal Workspace' },
                    produktivitas: { id: 'Produktivitas & Rencana Harian', en: 'Daily Productivity & Plans' },
                    menu_utama: { id: 'Menu Utama', en: 'Main Menu' },
                    papan_kanban: { id: 'Papan Kanban', en: 'Kanban Board' },
                    agenda_kalender: { id: 'Agenda Kalender', en: 'Calendar Agenda' },
                    daftar_agenda: { id: 'Daftar Agenda', en: 'Agenda List' },
                    target_hari_ini: { id: 'Target Hari Ini', en: 'Today\'s Target' },
                    target_info: { id: '3 dari 8 tugas telah selesai.', en: '3 of 8 tasks completed.' },
                    manajemen_tugas: { id: 'Manajemen Tugas', en: 'Task Management' },
                    sapaan: { id: 'Halo,', en: 'Hello,' },
                    sub_sapaan: { id: 'Semangat menuntaskan agenda dan target produktivitas Anda hari ini!', en: 'Have a productive day completing your schedule and goals!' },
                    tambah_tugas: { id: 'Tambah Tugas Baru', en: 'Add New Task' },
                    tambah_agenda: { id: 'Tambah Agenda Baru', en: 'Add New Agenda' },
                    cari_tugas: { id: 'Cari tugas...', en: 'Search tasks...' },
                    cari_agenda: { id: 'Cari agenda...', en: 'Search agenda...' },
                    suasana_tema: { id: 'Ubah Suasana', en: 'Mood Switcher' },
                    mode_gelap: { id: 'Mode Gelap', en: 'Dark Mode' },
                    mode_terang: { id: 'Mode Terang', en: 'Light Mode' },
                    filter_prioritas: { id: 'Filter Prioritas:', en: 'Priority Filter:' },
                    filter_status: { id: 'Filter Status:', en: 'Status Filter:' },
                    urutkan: { id: 'Urutkan:', en: 'Sort by:' },
                    deadline_terdekat: { id: 'Deadline Terdekat', en: 'Earliest Deadline' },
                    deadline_terjauh: { id: 'Deadline Terjauh', en: 'Furthest Deadline' },
                    semua: { id: 'Semua', en: 'All' },
                    tinggi: { id: 'Tinggi', en: 'High' },
                    sedang: { id: 'Sedang', en: 'Medium' },
                    rendah: { id: 'Rendah', en: 'Low' },
                    kolom_belum: { id: 'Belum Dikerjakan', en: 'To Do' },
                    kolom_sedang: { id: 'Sedang Dikerjakan', en: 'In Progress' },
                    kolom_selesai: { id: 'Selesai', en: 'Completed' },
                    lihat_detail: { id: 'Lihat Detail', en: 'View Details' },
                    edit_tugas: { id: 'Edit Tugas', en: 'Edit Task' },
                    hapus_tugas: { id: 'Hapus Tugas', en: 'Delete Task' },
                    subtugas: { id: 'Subtugas:', en: 'Subtasks:' },
                    tenggat: { id: 'Tenggat', en: 'Due' },
                    kemajuan: { id: 'Kemajuan', en: 'Progress' },
                    keluar: { id: 'Keluar', en: 'Sign Out' },
                    simpan: { id: 'Simpan Tugas', en: 'Save Task' },
                    batal: { id: 'Batal', en: 'Cancel' }
                },
                t(key) {
                    if (this.dict && this.dict[key]) {
                        return this.dict[key][this.lang] || this.dict[key]['id'] || key;
                    }
                    return key;
                },
                setTheme(val) {
                    this.theme = val;
                    localStorage.setItem('flowtask_theme', val);
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
                toggleLang() {
                    this.lang = (this.lang === 'id') ? 'en' : 'id';
                    localStorage.setItem('flowtask_lang', this.lang);
                },
                toggleDropdown(id) {
                    this.activeDropdown = (this.activeDropdown === id) ? null : id;
                },
                closeDropdown() {
                    this.activeDropdown = null;
                },
                openDetail(title, category, description, priority, status, dueDate, id = null) {
                    this.activeTask = { id, title, category, description, priority, status, dueDate };
                    this.isDetailModalOpen = true;
                    this.closeDropdown();
                },
                openEditModal(task) {
                    this.editTask = {
                        id: task.id,
                        title: task.title || '',
                        category: task.category || 'Pengembangan Web',
                        description: task.description || '',
                        priority: task.priority || 'Sedang',
                        status: task.status || 'Belum Dikerjakan',
                        due_date: task.due_date ? (task.due_date.includes('T') ? task.due_date.split('T')[0] : task.due_date) : '',
                        subtasks: Array.isArray(task.subtasks) ? JSON.parse(JSON.stringify(task.subtasks)) : []
                    };
                    this.isEditModalOpen = true;
                    this.closeDropdown();
                },
                addSubtask() {
                    if (!this.editTask.subtasks) this.editTask.subtasks = [];
                    this.editTask.subtasks.push({ title: '', completed: false });
                },
                removeSubtask(index) {
                    this.editTask.subtasks.splice(index, 1);
                },
                getEditProgressPercent() {
                    if (!this.editTask.subtasks || this.editTask.subtasks.length === 0) {
                        return this.editTask.status === 'Selesai' ? 100 : 0;
                    }
                    const done = this.editTask.subtasks.filter(s => s.completed).length;
                    return Math.round((done / this.editTask.subtasks.length) * 100);
                },
                getEditProgressText() {
                    if (!this.editTask.subtasks || this.editTask.subtasks.length === 0) {
                        return 'Belum ada rincian butir subtugas';
                    }
                    const done = this.editTask.subtasks.filter(s => s.completed).length;
                    return done + ' dari ' + this.editTask.subtasks.length + ' subtugas selesai (' + this.getEditProgressPercent() + '%)';
                },
                confirmDelete(taskOrTitle, id = null) {
                    if (typeof taskOrTitle === 'object' && taskOrTitle !== null) {
                        this.activeTask = { id: taskOrTitle.id, title: taskOrTitle.title };
                    } else {
                        this.activeTask = { id: id, title: taskOrTitle };
                    }
                    this.isDeleteModalOpen = true;
                    this.closeDropdown();
                },
                // Interaktivitas Drag & Drop Kartu Kanban
                handleDragStart(event, taskId) {
                    this.draggedTaskId = taskId;
                    event.dataTransfer.setData('text/plain', taskId);
                    event.dataTransfer.effectAllowed = 'move';
                    if (event.target) {
                        event.target.classList.add('opacity-50');
                    }
                },
                handleDragEnd(event) {
                    if (event.target) {
                        event.target.classList.remove('opacity-50');
                    }
                    this.draggedTaskId = null;
                },
                async handleDrop(event, targetStatus) {
                    const taskId = event.dataTransfer.getData('text/plain') || this.draggedTaskId;
                    if (!taskId) return;
                    await this.moveTaskStatus(taskId, targetStatus);
                },
                async moveTaskStatus(taskId, newStatus) {
                    try {
                        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
                        const response = await fetch('/tasks/' + taskId + '/status', {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ status: newStatus })
                        });

                        const result = await response.json();
                        if (response.ok && result.success) {
                            this.statusToastMessage = 'Status tugas berhasil diperbarui ke: ' + newStatus;
                            this.showStatusToast = true;
                            setTimeout(() => { this.showStatusToast = false; }, 3000);
                            window.location.reload();
                        } else {
                            alert('Gagal memperbarui status tugas: ' + (result.message || 'Terjadi kesalahan'));
                        }
                    } catch (error) {
                        console.error('Error saat update status tugas:', error);
                        window.location.reload();
                    }
                }
            };
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('flowtaskApp', flowtaskApp);
        });
    </script>

    <!-- Alpine.js untuk Reaktivitas Global -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        [x-cloak] { display: none !important; }

        :root {
            --color-primary: #4f46e5;
            --color-primary-hover: #4338ca;
            --color-primary-light: #e0e7ff;
            --color-primary-glow: rgba(79, 70, 229, 0.25);
            --color-accent-badge: #eef2ff;
            --color-accent-text: #4338ca;
        }

        .dark {
            --color-primary: #6366f1;
            --color-primary-hover: #818cf8;
            --color-primary-light: #312e81;
            --color-primary-glow: rgba(99, 102, 241, 0.4);
            --color-accent-badge: #1e1b4b;
            --color-accent-text: #c7d2fe;
        }

        [data-theme="emerald"] {
            --color-primary: #059669;
            --color-primary-hover: #047857;
            --color-primary-light: #d1fae5;
            --color-primary-glow: rgba(5, 150, 105, 0.25);
            --color-accent-badge: #ecfdf5;
            --color-accent-text: #065f46;
        }
        .dark [data-theme="emerald"], .dark[data-theme="emerald"] {
            --color-primary: #10b981;
            --color-primary-hover: #34d399;
            --color-primary-light: #064e3b;
            --color-primary-glow: rgba(16, 185, 129, 0.4);
            --color-accent-badge: #022c22;
            --color-accent-text: #a7f3d0;
        }

        [data-theme="rose"] {
            --color-primary: #e11d48;
            --color-primary-hover: #be123c;
            --color-primary-light: #ffe4e6;
            --color-primary-glow: rgba(225, 29, 72, 0.25);
            --color-accent-badge: #fff1f2;
            --color-accent-text: #9f1239;
        }
        .dark [data-theme="rose"], .dark[data-theme="rose"] {
            --color-primary: #f43f5e;
            --color-primary-hover: #fb7185;
            --color-primary-light: #4c0519;
            --color-primary-glow: rgba(244, 63, 94, 0.4);
            --color-accent-badge: #4c0519;
            --color-accent-text: #fecdd3;
        }

        [data-theme="amber"] {
            --color-primary: #d97706;
            --color-primary-hover: #b45309;
            --color-primary-light: #fef3c7;
            --color-primary-glow: rgba(217, 119, 6, 0.25);
            --color-accent-badge: #fffbeb;
            --color-accent-text: #92400e;
        }
        .dark [data-theme="amber"], .dark[data-theme="amber"] {
            --color-primary: #f59e0b;
            --color-primary-hover: #fbbf24;
            --color-primary-light: #451a03;
            --color-primary-glow: rgba(245, 158, 11, 0.4);
            --color-accent-badge: #451a03;
            --color-accent-text: #fde68a;
        }

        [data-theme="cyan"] {
            --color-primary: #0891b2;
            --color-primary-hover: #0e7490;
            --color-primary-light: #cffafe;
            --color-primary-glow: rgba(8, 145, 178, 0.25);
            --color-accent-badge: #ecfeff;
            --color-accent-text: #155e75;
        }
        .dark [data-theme="cyan"], .dark[data-theme="cyan"] {
            --color-primary: #06b6d4;
            --color-primary-hover: #22d3ee;
            --color-primary-light: #164e63;
            --color-primary-glow: rgba(6, 182, 212, 0.4);
            --color-accent-badge: #083344;
            --color-accent-text: #a5f3fc;
        }

        .bg-theme-dynamic { background-color: var(--color-primary); }
        .text-theme-dynamic { color: var(--color-primary); }
        .border-theme-dynamic { border-color: var(--color-primary); }
        .hover-bg-theme-dynamic:hover { background-color: var(--color-primary-hover); }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 font-sans text-slate-800 dark:text-slate-100 antialiased selection:bg-indigo-500 selection:text-white transition-colors duration-200"
      :data-theme="theme"
      x-data="flowtaskApp()">

    {{ $slot }}

    <!-- Toast Notifikasi Sinkronisasi Status (Alpine.js) -->
    <div x-show="showStatusToast" 
         x-cloak 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-50 px-4 py-3 rounded-2xl bg-slate-900 text-white shadow-xl flex items-center gap-2.5 text-xs font-semibold border border-slate-700 animate-fadeIn">
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
        <span x-text="statusToastMessage">Status tugas berhasil diperbarui</span>
    </div>

</body>
</html>
