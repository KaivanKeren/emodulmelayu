@extends('layouts.admin')

@section('title', 'Edit Material')

@section('content')
    <link rel="stylesheet" href="/assets/extensions/filepond/filepond.css">
    <link rel="stylesheet" href="/assets/extensions/filepond-plugin-image-preview/filepond-plugin-image-preview.css">

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-lg rounded-2xl">
                <div class="p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Edit Material</h2>
                    </div>

                    <form action="{{ route('materials.update', $material->id) }}" method="POST"
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
                                <input type="text" name="title" id="title" placeholder="Judul Material"
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
                                <textarea name="description" id="description" rows="4" placeholder="Deskripsi Material"
                                    class="block w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500">{{ old('description', $material->description) }}</textarea>
                            </div>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Asset -->
                        <div class="rounded-md">
                            <label for="asset" class="block text-sm font-medium text-gray-700">Upload File</label>
                            <div class="mt-2">
                                <input type="file" name="asset" id="filepond" class="filepond"
                                    accept="application/pdf">
                            </div>
                            <p class="mt-1 text-sm text-gray-500">File PDF saat ini:
                                <a href="{{ asset('storage/' . $material->asset) }}" class="text-orange-500 hover:underline"
                                    target="_blank">
                                    {{ basename($material->asset) }}
                                </a>
                            </p>
                            @error('asset')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Model -->
                        <div class="rounded-md">
                            <label for="model_id" class="block text-sm font-medium text-gray-700">Pilih Model</label>
                            <select name="model_id" id="model_id"
                                class="block w-full mt-2 px-4 py-2 bg-white border border-gray-300 rounded-lg focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                <option value="">-- Pilih Model --</option>
                                @foreach ($models as $model)
                                    <option value="{{ $model->id }}"
                                        {{ old('model_id', $material->model_id) == $model->id ? 'selected' : '' }}>
                                        {{ $model->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('model_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('materials.index') }}"
                                class="px-6 py-2.5 rounded-full text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                Batal
                            </a>
                            <button type="submit"
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
    <script src="/assets/extensions/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.js"></script>
    <script>
        // Register plugins
        FilePond.registerPlugin(FilePondPluginFileValidateType);

        // Get a reference to the file input element
        const inputElement = document.querySelector('input[type="file"].filepond');

        // Create a FilePond instance
        const pond = FilePond.create(inputElement, {
            acceptedFileTypes: ['application/pdf'],
            fileValidateTypeDetectType: (source, type) => new Promise((resolve, reject) => {
                // Do custom type detection here and return with promise
                resolve(type);
            }),
            labelIdle: 'Seret & Lepaskan file Anda atau <span class="filepond--label-action">Jelajahi</span>',
            labelFileTypeNotAllowed: 'File harus berformat PDF',
            fileValidateTypeLabelExpectedTypes: 'File harus berformat PDF',
            // Disable image preview plugins
            allowImagePreview: false,
            allowImageFilter: false,
            allowImageExifOrientation: false,
            allowImageCrop: false,
            allowImageResize: false,
            allowImageTransform: false,
            allowImageEdit: false,
        });
    </script>
@endsection
