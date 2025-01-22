@extends('layouts.admin')

@section('title', 'Edit Material')

@section('content')
    <link rel="stylesheet" href="/assets/extensions/filepond/filepond.css">
    {{-- <link rel="stylesheet" href="/assets/extensions/filepond-plugin-image-preview/filepond-plugin-image-preview.css"> --}}

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-lg rounded-2xl">
                <div class="p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Edit Material</h2>
                    </div>

                    <!-- Progress Bar (Initially Hidden) -->
                    <div id="uploadProgress" class="hidden mb-4">
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div id="progressBar" class="bg-gradient-to-r from-orange-500 to-yellow-500 h-2.5 rounded-full"
                                style="width: 0%"></div>
                        </div>
                        <p id="progressText" class="text-sm text-gray-600 mt-2">Mengunggah: 0%</p>
                    </div>

                    <form id="materialForm" action="{{ route('materials.update', $material->id) }}" method="POST"
                        enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Title -->
                        <div class="rounded-md">
                            <div
                                class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                <span class="text-gray-400">
                                    <i data-lucide="file-text" class="w-5 h-5"></i>
                                </span>
                                <input type="text" required name="title" id="title" placeholder="Judul Material"
                                    value="{{ old('title', $material->title) }}"
                                    class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                            </div>
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="rounded-md">
                            <div class="flex flex-col">
                                <textarea name="description" required id="description" rows="4" placeholder="Deskripsi Material"
                                    class="block w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500">{{ old('description', $material->description) }}</textarea>
                            </div>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Assets -->
                        <div class="rounded-md space-y-4">
                            <label class="block text-sm font-medium text-gray-700">File yang Ada</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($material->assets as $asset)
                                    <a href="{{ asset('storage/' . $asset) }}"
                                        class="text-sm font-medium text-orange-500 hover:underline" target="_blank">
                                        {{ basename($asset) }}
                                    </a>
                                @endforeach
                            </div>
                            <div class="mt-4">
                                <label for="assets" class="block text-sm font-medium text-gray-700">Upload File
                                    Baru</label>
                                <div class="mt-2">
                                    <input type="file" name="assets[]" id="filepond" class="basic-filepond"
                                        required="false" accept="application/pdf,.mp4" multiple>
                                </div>
                                @error('assets.*')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('materials.index') }}"
                                class="px-6 py-2.5 rounded-full text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                Batal
                            </a>
                            <button type="submit" id="submitBtn"
                                class="px-6 py-2.5 rounded-full text-white bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/extensions/filepond/filepond.js"></script>
    <script src="/assets/static/js/pages/filepond.js"></script>

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
                credits: false
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
                        try {
                            // Check if the response is JSON
                            const contentType = xhr.getResponseHeader('Content-Type');
                            if (contentType && contentType.includes('application/json')) {
                                const response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    window.location.href = form.dataset.redirectUrl || '/materials';
                                    return;
                                }
                            }

                            // If we get here, either the response wasn't JSON or success was false
                            throw new Error(
                            'Upload failed: Server returned an unexpected response');
                        } catch (error) {
                            console.error('Response parsing error:', error);
                            console.error('Server response:', xhr.responseText);
                            handleError('Terjadi kesalahan saat memproses respons server.');
                        }
                    };

                    xhr.onerror = function() {
                        handleError('Gagal menghubungi server. Periksa koneksi internet Anda.');
                    };

                    xhr.send(formData);

                } catch (error) {
                    handleError('Terjadi kesalahan saat mengunggah file.');
                }
            });

            function handleError(message) {
                // Reset the form state
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                progressDiv.classList.add('hidden');
                progressBar.style.width = '0%';
                progressText.textContent = 'Mengunggah: 0%';

                window.location.href = '/admin/materials';
            }
        });
    </script>
@endsection
