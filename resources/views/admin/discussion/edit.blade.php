{{-- resources/views/discussions/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Diskusi')

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="md:flex md:items-center md:justify-between mb-6">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Edit Diskusi
                    </h2>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <a href="{{ route('discussions.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                                clip-rule="evenodd" />
                        </svg>
                        Kembali
                    </a>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <form action="{{ route('discussions.update', $discussion->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="px-4 py-5 sm:p-6">
                        <!-- Title Field -->
                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-gray-700 my-2">
                                Judul Diskusi
                            </label>
                            <div class="rounded-md">
                                <div
                                    class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                    <span class="text-gray-400">
                                        <i data-lucide="file-text" class="w-5 h-5"></i>
                                    </span>
                                    <input type="text" required name="title" id="title"
                                        class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none"
                                        value="{{ old('title', $discussion->title) }}" placeholder="Masukkan judul diskusi">
                                </div>
                                @error('title')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Content Field -->
                            <div class="mb-6 mt-5">
                                <label for="content" class="block my-2 text-sm font-medium text-gray-700">
                                    Konten Diskusi
                                </label>
                                <div class="rounded-md">
                                    <div
                                        class="flex items-center border border-gray-300 rounded-lg px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                        <textarea name="content" required id="content" rows="8"
                                            class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none"
                                            placeholder="Tuliskan isi diskusi di sini...">{{ old('content', $discussion->content) }}</textarea>
                                    </div>
                                    @error('content')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                                <button type="button" onclick="window.location='{{ route('discussions.index') }}'"
                                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-2">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Simpan Perubahan
                                </button>
                            </div>
                </form>
            </div>
        </div>
    </div>
@endsection
