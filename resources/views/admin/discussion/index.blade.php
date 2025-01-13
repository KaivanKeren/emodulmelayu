@extends('layouts.admin')

@section('title', 'Diskusi')

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-900">Diskusi</h1>
                @auth
                    <a href="{{ route('discussions.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Buat Diskusi Baru
                    </a>
                @endauth
            </div>

            @if (session('success'))
                <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    @foreach ($discussions as $discussion)
                        <li>
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ route('discussions.show', $discussion) }}"
                                            class="text-lg font-medium text-indigo-600 hover:text-indigo-700 hover:underline">
                                            {{ $discussion->title }}
                                        </a>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Oleh {{ $discussion->user->name }} •
                                            {{ $discussion->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    @can('update', $discussion)
                                        <div class="flex space-x-2">
                                            <a href="{{ route('discussions.edit', $discussion) }}"
                                                class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                                Edit
                                            </a>
                                            <form action="{{ route('discussions.destroy', $discussion) }}" method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus diskusi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-1 border border-red-300 rounded-md text-sm font-medium text-red-700 bg-white hover:bg-red-50">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    @endcan
                                </div>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-600 line-clamp-2">
                                        {{ $discussion->content }}
                                    </p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="mt-4">
                {{ $discussions->links() }}
            </div>
        </div>
    </div>
@endsection
