<!-- ========================================================================================= -->
<!-- VIEW BLADE: calendar.blade.php (Halaman Kalender Agenda FlowTask)                         -->
<!-- ========================================================================================= -->
<!-- Menampilkan jadwal tenggat waktu tugas dalam kalender interaktif bulanan.                  -->
<!-- Terhubung langsung dengan database MySQL melalui CalendarController@index.                 -->
<!-- ========================================================================================= -->

<x-layouts.app :title="'FlowTask - Agenda Kalender'">

    <div class="min-h-screen flex bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased selection:bg-indigo-500 selection:text-white transition-colors duration-200">
        
        <!-- 1. Sidebar Navigasi Kiri (Modular Component) -->
        <x-sidebar active="calendar" />

        <!-- 2. Area Konten Utama -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            
            <!-- Navbar Header Atas dengan Tombol Tambah Tugas Sungguhan -->
            <x-navbar :title="'Agenda Kalender'" 
                      :breadcrumb="'Agenda Kalender'" 
                      :ctaText="'tambah_agenda'" 
                      :ctaAction="'isNewTaskModalOpen = true'" />

            <!-- Main Content: Komponen Kalender Dinamis (Real JavaScript Calendar) -->
            <main class="p-4 sm:p-6 lg:p-8 space-y-6">
                <!-- Merender tugas yang diambil dari database MySQL -->
                <x-calendar.view :tasks="$tasks ?? []" />
            </main>

        </div>

    </div>

    <!-- Modals (Tambah, Edit, Detail, Hapus) -->
    <x-modals.new-task />
    <x-modals.edit-task />
    <x-modals.task-detail />
    <x-modals.delete-task />

</x-layouts.app>
