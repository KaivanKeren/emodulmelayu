<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - E-Modul Budaya Melayu Riau</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js"></script>
    <script>
        window.onload = () => lucide.createIcons();
    </script>
</head>

<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 min-h-screen">
            <div class="p-6">
                <h1 class="text-xl font-bold text-gray-900">Admin Panel</h1>
            </div>
            <nav class="space-y-1 px-3">
                <a href="#"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg bg-gray-100 text-gray-900">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>
                    Dashboard
                </a>

                <a href="#"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:bg-gray-50">
                    <i data-lucide="users" class="w-5 h-5 mr-3"></i>
                    Pengguna
                </a>

                <a href="#"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:bg-gray-50">
                    <i data-lucide="book" class="w-5 h-5 mr-3"></i>
                    Materi
                </a>

                <a href="#"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:bg-gray-50">
                    <i data-lucide="file-text" class="w-5 h-5 mr-3"></i>
                    Assessment
                </a>

                <a href="#"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:bg-gray-50">
                    <i data-lucide="message-circle" class="w-5 h-5 mr-3"></i>
                    Diskusi
                </a>

                <a href="#"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:bg-gray-50">
                    <i data-lucide="calendar" class="w-5 h-5 mr-3"></i>
                    Kalender
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <!-- Top Bar -->
            <div class="flex justify-between items-center mb-8">
                <div class="relative">
                    <input type="text" placeholder="Cari..."
                        class="w-96 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i data-lucide="search" class="h-5 w-5 text-gray-400 absolute left-3 top-2.5"></i>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="p-2 text-gray-400 hover:text-gray-500">
                        <i data-lucide="bell" class="h-6 w-6"></i>
                    </button>
                    <span class="text-gray-700">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-2 text-gray-400 hover:text-gray-500">
                            <i data-lucide="log-out" class="h-6 w-6"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <div class="flex justify-between items-center">
                        <h3 class="text-gray-500 text-sm">Total Pengguna</h3>
                        <span class="bg-gray-100 p-2 rounded-lg">
                            <i data-lucide="users" class="w-5 h-5 text-gray-600"></i>
                        </span>
                    </div>
                    <p class="text-2xl font-bold mt-4">{{ $total_users }}</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <div class="flex justify-between items-center">
                        <h3 class="text-gray-500 text-sm">Materi Aktif</h3>
                        <span class="bg-gray-100 p-2 rounded-lg">
                            <i data-lucide="book" class="w-5 h-5 text-gray-600"></i>
                        </span>
                    </div>
                    <p class="text-2xl font-bold mt-4">{{ $materials }}</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <div class="flex justify-between items-center">
                        <h3 class="text-gray-500 text-sm">Assessment</h3>
                        <span class="bg-gray-100 p-2 rounded-lg">
                            <i data-lucide="file-text" class="w-5 h-5 text-gray-600"></i>
                        </span>
                    </div>
                    <p class="text-2xl font-bold mt-4">{{ $assessments }}</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <div class="flex justify-between items-center">
                        <h3 class="text-gray-500 text-sm">Diskusi Aktif</h3>
                        <span class="bg-gray-100 p-2 rounded-lg">
                            <i data-lucide="message-circle" class="w-5 h-5 text-gray-600"></i>
                        </span>
                    </div>
                    <p class="text-2xl font-bold mt-4">{{ $discussions }}</p>
                </div>
            </div>

            <!-- User Management Section -->
            <div class="flex-1 bg-white rounded-xl shadow-sm p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-semibold">Manajemen Pengguna</h2>
                    <div class="flex space-x-3">
                        <button class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
                            Filter
                        </button>
                        <button class="px-4 py-2 text-white bg-orange-500 rounded-lg hover:bg-orange-600">
                            Tambah User
                        </button>
                    </div>
                </div>

                <!-- User Table -->
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-gray-500 text-sm">
                            <th class="pb-4">Nama</th>
                            <th class="pb-4">Role</th>
                            <th class="pb-4">Sekolah</th>
                            <th class="pb-4">Status</th>
                            <th class="pb-4">Tanggal Daftar</th>
                            <th class="pb-4"></th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @foreach ($users as $user)
                            <tr class="border-t">
                                <td class="py-4">{{ $user->name }}</td>
                                <td>{{ $user->role }}</td>
                                <td>{{ $user->school }}</td>
                                <td>
                                    <span
                                        class="px-2 py-1 text-xs font-medium 
                        @if ($user->status === 'active') text-green-700 bg-green-100
                        @elseif ($user->status === 'pending') text-yellow-700 bg-yellow-100
                        @else text-red-700 bg-red-100 @endif 
                        rounded-full">
                                        {{ $user->status }}
                                    </span>
                                </td>
                                <td>{{ $user->created_at->format('d M Y') }}</td>
                                <td>
                                    <button class="text-gray-400 hover:text-gray-500">
                                        <i data-lucide="more-vertical" class="w-5 h-5"></i>

                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
        </main>
    </div>
</body>

</html>
