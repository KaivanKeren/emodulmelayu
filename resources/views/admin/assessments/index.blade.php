@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="flex-1 bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold">Manajemen Assessment</h2>
            <div class="flex space-x-3">
                <button class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Filter
                </button>
                <a href="{{ route('users.create') }}">
                    <button class="px-4 py-2 text-white bg-orange-500 rounded-lg hover:bg-orange-600">
                        Tambah Assessment
                    </button>
                </a>
            </div>
        </div>
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm">
                    <th class="pb-4">Judul</th>
                    <th class="pb-4">Kategori</th>
                    <th class="pb-4">Status</th>
                    <th class="pb-4">Tanggal Dibuat</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                @foreach ($assessments as $assessment)
                    <tr class="border-t">
                        <td class="py-4">{{ $assessment->title }}</td>
                        <td>{{ $assessment->category }}</td>
                        <td>
                            <span
                                class="px-2 py-1 text-xs font-medium 
                            @if ($assessment->status === 'terbuka') text-green-700 bg-green-100
                            @elseif ($assessment->status === 'belum terbuka') text-yellow-700 bg-yellow-100
                            @else text-red-700 bg-red-100 @endif 
                            rounded-full">
                                {{ $assessment->status }}
                            </span>
                        </td>
                        <td>{{ $assessment->created_at->format('d M Y') }}</td>
                        <td>
                            <button class="text-gray-400 hover:text-gray-500">
                                <i data-lucide="more-vertical" class="w-5 h-5"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @endsection
