@extends('layouts.admin')

@section('title', 'Create Material')

@section('content')
    <link rel="stylesheet" href="/assets/extensions/filepond/filepond.css">

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-lg rounded-2xl">
                <div class="p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Buat Material Baru</h2>
                    </div>

                    <!-- Progress Bar (Initially Hidden) -->
                    <div id="uploadProgress" class="hidden mb-4">
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div id="progressBar" class="bg-gradient-to-r from-orange-500 to-yellow-500 h-2.5 rounded-full"
                                style="width: 0%"></div>
                        </div>
                        <p id="progressText" class="text-sm text-gray-600 mt-2">Mengunggah: 0%</p>
                    </div>

                    <form id="materialForm" action="{{ route('materials.store') }}" method="POST"
                        enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        <!-- Title -->
                        <div class="rounded-md">
                            <label for="title" class="block text-sm font-medium text-gray-700 my-2">
                                Judul Materi
                            </label>
                            <div
                                class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                <span class="text-gray-400">
                                    <i data-lucide="file-text" class="w-5 h-5"></i>
                                </span>
                                <input type="text" name="title" required id="title"
                                    placeholder="Masukkan judul materi" value="{{ old('title') }}"
                                    class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                            </div>
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="rounded-md">
                            <label for="description" class="block text-sm font-medium text-gray-700 my-2">
                                Deskripsi
                            </label>
                            <div class="flex flex-col">
                                <textarea name="description" id="description" required rows="4" placeholder="Masukkan deskripsi materi"
                                    class="block w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500">{{ old('description') }}</textarea>
                            </div>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Assets -->
                        <div class="rounded-md">
                            <label for="assets" class="block text-sm font-medium text-gray-700">Unggah File</label>
                            <div class="mt-2 items-center">
                                <input type="file" class="basic-filepond" name="assets" multiple
                                    accept=".pdf,.mp4,.mov,.avi" id="assets">
                            </div>
                            @error('assets')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('assets.*')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('materials.index') }}"
                                class="px-6 py-2.5 rounded-full text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                Batal
                            </a>
                            <button type="submit" id="submitBtn"
                                class="px-6 py-2.5 rounded-full text-white bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                Buat Material
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/extensions/filepond/filepond.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('materialForm');
            const progressDiv = document.getElementById('uploadProgress');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            const submitBtn = document.getElementById('submitBtn');

            // Initialize FilePond
            const pond = FilePond.create(document.querySelector('.basic-filepond'), {
                allowMultiple: true,
                acceptedFileTypes: ['application/pdf', 'video/mp4', 'video/quicktime', 'video/x-msvideo'],
                maxFileSize: '100MB',
            });

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                // Disable submit button
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');

                progressDiv.classList.remove('hidden');

                const formData = new FormData(form);

                // Get files from FilePond
                const pondFiles = pond.getFiles();
                pondFiles.forEach((pondFile, index) => {
                    formData.append(`assets[${index}]`, pondFile.file);
                });

                try {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', form.action);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                    xhr.upload.onprogress = function(e) {
                        if (e.lengthComputable) {
                            const percentComplete = (e.loaded / e.total) * 100;
                            progressBar.style.width = percentComplete + '%';
                            progressText.textContent =
                            `Mengunggah: ${Math.round(percentComplete)}%`;
                        }
                    };

                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                window.location.href = '{{ route('materials.index') }}';
                            }
                        } else {
                            throw new Error('Upload failed');
                        }
                    };

                    xhr.onerror = function() {
                        throw new Error('Upload failed');
                    };

                    xhr.send(formData);
                } catch (error) {
                    console.error('Error:', error);
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    progressDiv.classList.add('hidden');
                    alert('Terjadi kesalahan saat mengunggah. Silakan coba lagi.');
                }
            });
        });
    </script>
@endsection
