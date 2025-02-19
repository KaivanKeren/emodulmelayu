@extends('layouts.admin')

@section('title', 'Create Assessment')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.16.4/katex.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill/dist/quill.snow.css">
    <link rel="stylesheet" href="/assets/quill.css">

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-lg rounded-2xl">
                <div class="p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Buat Assessment Baru</h2>
                    </div>

                    <form action="{{ route('assessments.store') }}" enctype="multipart/form-data" method="POST"
                        class="space-y-8">
                        @csrf

                        <!-- Assessment Details -->
                        <div class="space-y-6">
                            <!-- Title -->
                            <div class="rounded-md">
                                <label for="title" class="block text-sm font-medium text-gray-700 my-2">
                                    Judul Assessment
                                </label>
                                <div
                                    class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                    <span class="text-gray-400">
                                        <i data-lucide="file-text" class="w-5 h-5"></i>
                                    </span>
                                    <input type="text" name="title" required id="title"
                                        placeholder="Masukkan judul assessment" value="{{ old('title') }}"
                                        class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                                </div>
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Category -->
                            <div class="rounded-md">
                                <label for="category" class="block text-sm font-medium text-gray-700 my-2">
                                    Kategori Assessment
                                </label>
                                <div
                                    class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                    <span class="text-gray-400">
                                        <i data-lucide="tag" class="w-5 h-5"></i>
                                    </span>
                                    <input type="text" name="category" required id="category"
                                        placeholder="Masukkan kategori assessment" value="{{ old('category') }}"
                                        class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                                </div>
                                @error('category')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="rounded-md">
                                <label for="status" class="block text-sm font-medium text-gray-700 my-2">
                                    Status Assessment
                                </label>
                                <div
                                    class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                    <span class="text-gray-400">
                                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                                    </span>
                                    <select name="status" id="status"
                                        class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                                        <option value="Belum Terbuka">Belum Terbuka</option>
                                        <option value="Terbuka">Terbuka</option>
                                        <option value="Terjawab">Terjawab</option>
                                        <option value="Selesai">Selesai</option>
                                    </select>
                                </div>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Questions Section -->
                        <h3 class="text-lg font-medium text-gray-900">Pertanyaan</h3>

                        <div id="questions_container" class="space-y-8">
                            <!-- Questions will be added here -->
                        </div>
                        <div class="space-y-6">
                            <div class="flex justify-end items-center">
                                <button type="button" id="add_question"
                                    class="px-4 py-2 rounded-full text-orange-600 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                    <i data-lucide="plus" class="w-5 h-5 inline-block mr-1"></i>
                                    Tambah Pertanyaan
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('assessments.index') }}"
                                class="px-6 py-2.5 rounded-full text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                Batal
                            </a>
                            <button type="submit"
                                class="px-6 py-2.5 rounded-full text-white bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                Buat Assessment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/create-question-asm.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.16.9/katex.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill-mathquill@1.0.2/dist/quill-mathquill.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill-image-resize-module@3.0.0/image-resize.min.js"></script>
    <script src="https://apis.google.com/js/api.js"></script>
    <script src="https://accounts.google.com/gsi/client"></script>

@endsection
