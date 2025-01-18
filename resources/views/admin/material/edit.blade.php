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

                    <form action="{{ route('materials.update', $material->id) }}" method="POST" enctype="multipart/form-data"
                        class="space-y-6">
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

                        <!-- Assets -->
                        <div class="rounded-md space-y-4">
                            {{-- <label class="block text-sm font-medium text-gray-700">File yang Ada</label> --}}
                            {{-- <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($material->assets as $asset)
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-gray-400">
                                                @if (str_contains($asset, '.pdf'))
                                                    <i data-lucide="file" class="w-5 h-5"></i>
                                                @elseif (str_contains($asset, '.mp4'))
                                                    <i data-lucide="video" class="w-5 h-5"></i>
                                                @endif
                                            </span>
                                            <div>
                                                <a href="{{ asset('storage/' . $asset) }}"
                                                    class="text-sm font-medium text-orange-500 hover:underline"
                                                    target="_blank">
                                                    {{ basename($asset) }}
                                                </a>
                                            </div>
                                        </div>
                                        <button type="button"
                                            onclick="deleteAsset('{{ $asset }}', {{ $material->id }})"
                                            class="text-red-500 hover:text-red-700">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div> --}}
                            <div class="mt-4">
                                <label for="assets" class="block text-sm font-medium text-gray-700">Upload File
                                    Baru</label>
                                <div class="mt-2">
                                    <input type="file" name="assets[]" id="filepond"
                                        class="basic-filepond" required="false" accept="application/pdf,.mp4" multiple>
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
    <script src="/assets/static/js/pages/filepond.js"></script>
    {{-- <script src="/assets/extensions/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.js"></script> --}}
    {{-- <script>
        // Function to delete asset
        function deleteAsset(assetPath, materialId) {
            if (confirm('Apakah Anda yakin ingin menghapus file ini?')) {
                fetch("{{ route('materials.delete.asset') }}", {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            path: assetPath,
                            material_id: materialId, // Kirimkan ID material
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload(); // Refresh halaman setelah berhasil menghapus
                        } else {
                            alert('Gagal menghapus file. Silakan coba lagi.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan. Silakan coba lagi.');
                    });
            }
        }
    </script> --}}
@endsection
