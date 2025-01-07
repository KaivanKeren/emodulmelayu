<aside class="w-64 bg-white border-r border-gray-200 min-h-screen flex flex-col">
    <div class="p-6">
        <h1 class="text-xl font-bold text-gray-900">Admin Panel</h1>
    </div>
    <nav class="space-y-1 px-3 flex-grow">
        @php
            $dashboardRoute =
                auth()->user()->role === 'siswa'
                    ? 'studentDashboard'
                    : (auth()->user()->role === 'guru'
                        ? 'teacherDashboard'
                        : (auth()->user()->role === 'admin'
                            ? 'adminDashboard'
                            : 'dashboard'));
        @endphp

        <a href="{{ route($dashboardRoute) }}"
            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
        {{ Request::routeIs($dashboardRoute) ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">
            <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>
            Dashboard
        </a>

        <a href="{{ route('users.index') }}"
            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            {{ Request::is('admin/users*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">
            <i data-lucide="users" class="w-5 h-5 mr-3"></i>
            Pengguna
        </a>

        <a href="#"
            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            {{ Request::is('admin/assessments*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">
            <i data-lucide="file-text" class="w-5 h-5 mr-3"></i>
            Assessment
        </a>
        
        <a href="#"
            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            {{ Request::is('admin/models*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">
            <i data-lucide="box" class="w-5 h-5 mr-3"></i>
            Model AR
        </a>

        <a href="#"
            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            {{ Request::is('admin/materials*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">
            <i data-lucide="book" class="w-5 h-5 mr-3"></i>
            Materi
        </a>


        <a href="#"
            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            {{ Request::is('admin/discussions*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">
            <i data-lucide="message-circle" class="w-5 h-5 mr-3"></i>
            Diskusi
        </a>

        <a href="#"
            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            {{ Request::is('admin/calendar*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50' }}">
            <i data-lucide="calendar" class="w-5 h-5 mr-3"></i>
            Kalender
        </a>
    </nav>
    <div class="p-3">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="flex items-center px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100">
                <i data-lucide="log-out" class="w-5 h-5 mr-3"></i>
                Logout
            </button>
        </form>
    </div>
</aside>
