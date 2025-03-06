<!-- resources/views/components/sidebar.blade.php -->
<div x-data="{ sidebarOpen: false }" class="relative">
    <!-- Mobile hamburger -->
    <button @click="sidebarOpen = !sidebarOpen"
        class="lg:hidden fixed top-4 left-4 z-20 p-2 rounded-md text-gray-500 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:ring-2 focus:ring-inset focus:ring-indigo-500">
        <span class="sr-only">Open sidebar</span>
        <i x-show="!sidebarOpen" data-lucide="menu" class="w-6 h-6"></i>
        <i x-show="sidebarOpen" data-lucide="x" class="w-6 h-6"></i>
    </button>

    <!-- Sidebar backdrop -->
    <div x-show="sidebarOpen" class="fixed inset-0 z-10 bg-gray-600 bg-opacity-50 transition-opacity lg:hidden"
        @click="sidebarOpen = false">
    </div>

    <!-- Sidebar -->
    <aside :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }"
        class="fixed top-0 left-0 z-20 w-64 h-full bg-white border-r border-gray-200 transform transition-transform duration-200 ease-in-out lg:translate-x-0 lg:static lg:h-screen">
        <div class="p-6">
            @if (auth()->user()->role === 'Admin')
                <h1 class="text-xl font-bold text-gray-900">Admin Panel</h1>
            @elseif (auth()->user()->role === 'Guru')
                <h1 class="text-xl font-bold text-gray-900">Guru Panel</h1>
            @endif
        </div>

        <nav class="space-y-1 px-3 flex-grow">
            @if (auth()->user()->role === 'Guru')
                <!-- Only show the Assessment menu for teachers -->
                <a href="{{ route('teacherDashboard') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
                    {{ Request::is('guru/assessments*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">
                    <i data-lucide="file-text" class="w-5 h-5 mr-3"></i>
                    Assessment
                </a>
            @elseif (auth()->user()->role === 'Admin')
                <!-- Show the full menu for admins -->
                @php
                    $dashboardRoute = auth()->user()->role === 'siswa' ? 'studentDashboard' : 'adminDashboard';
                @endphp

                <a href="{{ route($dashboardRoute) }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
                    {{ Request::routeIs($dashboardRoute) ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>
                    Dashboard
                </a>

                <a href="{{ route('users.index') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg relative
                    {{ Request::is('admin/users*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">
                    <i data-lucide="users" class="w-5 h-5 mr-3"></i>
                    Pengguna
                    @if (isset($pendingUsers) && $pendingUsers > 0)
                        <span
                            class="absolute inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full right-3">
                            {{ $pendingUsers }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('schools.index') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
                    {{ Request::is('admin/schools*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">
                    <i data-lucide="school" class="w-5 h-5 mr-3"></i>
                    Sekolah
                </a>

                <a href="{{ route('materials.index') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
                {{ Request::is('admin/materials*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">
                    <i data-lucide="book" class="w-5 h-5 mr-3"></i>
                    Materi
                </a>

                <a href="{{ route('assessments.index') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
                {{ Request::is('admin/assessments*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">
                    <i data-lucide="file-text" class="w-5 h-5 mr-3"></i>
                    Assessment
                </a>

                <a href="{{ route('discussions.index') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
                    {{ Request::is('admin/discussions*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">
                    <i data-lucide="message-circle" class="w-5 h-5 mr-3"></i>
                    Diskusi
                </a>

                <a href="{{ route('calendar.index') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
                    {{ Request::is('admin/calendar*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">
                    <i data-lucide="calendar" class="w-5 h-5 mr-3"></i>
                    Kalender
                </a>
            @endif
        </nav>
    </aside>
</div>

<!-- Add this to your layout file where you include Alpine.js -->
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<!-- Add this to initialize Lucide icons -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
    });
</script>
