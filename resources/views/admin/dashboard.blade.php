@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
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
                {{-- <button class="px-4 py-2 text-white bg-orange-500 rounded-lg hover:bg-orange-600">
                    Tambah User
                </button> --}}
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
                </tr>
            </thead>
            <tbody class="text-sm">
                @foreach ($users->take(3) as $user)
                    <tr class="border-t">
                        <td class="py-4">{{ $user->name }}</td>
                        <td>{{ $user->role }}</td>
                        <td>{{ $user->school->name }}</td>
                        <td>
                            <span
                                class="px-2 py-1 text-xs font-medium 
                            @if ($user->status === 'active') text-green-700 bg-green-100
                            @elseif ($user->status === 'Pending') text-yellow-700 bg-yellow-100
                            @else text-red-700 bg-red-100 @endif 
                            rounded-full">
                                {{ $user->status }}
                            </span>
                        </td>
                        <td>{{ $user->created_at->format('d M Y') }}</td>
                    </tr>
                @endforeach

                @if ($users->count() > 3)
                    <tr class="border-t">
                        <td colspan="6" class="py-4 text-center">
                            <a href="{{ route('users.index') }}"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-800">
                                Lihat Semua Pengguna
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </a>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
@endsection
