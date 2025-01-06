@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
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
@endsection
