@extends('layouts.admin')

@section('title', 'Diskusi')

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-900">Diskusi</h1>
                <div class="flex justify-between gap-2 items-center">
                    @auth
                        <a href="{{ route('discussions.create') }}"
                            class="inline-flex items-center px-3 md:px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Buat Diskusi Baru
                        </a>
                        <a href="{{ route('filter-message.index') }}"
                            class="inline-flex text-xs items-center px-3 md:px-4 py-2 uppercase tracking-widest text-gray-600 bg-gray-200 rounded-lg hover:bg-gray-300 border border-gray-300">
                            Filter Pesan
                        </a>
                    </div>
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
                        <li class="border-b border-gray-200 last:border-b-0">
                            <div class="px-3 py-3 sm:px-4 sm:py-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ route('discussions.show', $discussion) }}"
                                            class="block text-base sm:text-lg font-medium text-indigo-600 hover:text-indigo-700 hover:underline truncate">
                                            {{ $discussion->title }}
                                        </a>
                                        <div
                                            class="mt-1 flex flex-wrap items-center gap-x-2 text-xs sm:text-sm text-gray-500">
                                            <span>Oleh {{ $discussion->user->name }}</span>
                                            <span>•</span>
                                            <span>{{ $discussion->created_at->diffForHumans() }}</span>
                                            <span>•</span>
                                            <span class="text-gray-700 font-semibold">{{ $discussion->participant_count }}
                                                partisipan</span>
                                        </div>
                                    </div>

                                    @can('update', $discussion)
                                        <div class="flex gap-2 mt-2 sm:mt-0">
                                            <a href="{{ route('discussions.edit', $discussion) }}"
                                                class="inline-flex items-center px-2.5 sm:px-3 py-1 border border-gray-300 rounded-md text-xs sm:text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                                Edit
                                            </a>
                                            <form action="{{ route('discussions.destroy', $discussion) }}" method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus diskusi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center px-2.5 sm:px-3 py-1 border border-red-300 rounded-md text-xs sm:text-sm font-medium text-red-700 bg-white hover:bg-red-50">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    @endcan
                                </div>

                                <div class="mt-2">
                                    <p class="text-xs sm:text-sm text-gray-600 line-clamp-2">
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
