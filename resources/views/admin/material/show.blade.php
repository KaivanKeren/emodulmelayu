@extends('layouts.admin')

@section('title', 'Detail Material')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <!-- Header Section -->
                <div class="border-b border-gray-200 bg-gradient-to-r from-white to-gray-50">
                    <div class="p-6 sm:px-8">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex-1">
                                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $material->title }}</h2>
                                <p class="mt-2 text-sm text-gray-500">Dibuat oleh {{ $material->user->name }} •
                                    {{ $material->created_at->format('d M Y') }}</p>
                            </div>
                            <a href="{{ route('materials.index') }}"
                                class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm hover:shadow">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Kembali
                            </a>
                        </div>
                        <div class="mt-4 prose prose-blue max-w-none">
                            <p class="text-gray-600 text-lg leading-relaxed">{{ $material->description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Files Preview Section -->
                <div class="p-6 sm:p-8">
                    <div class="space-y-8">
                        @php
                            // Handle both single asset (string) and multiple assets (array)
                            $assets = is_array(json_decode($material->assets))
                                ? json_decode($material->assets)
                                : [$material->assets];
                        @endphp

                        @foreach ($assets as $index => $asset)
                            @php
                                $extension = pathinfo($asset, PATHINFO_EXTENSION);
                                $isVideo = in_array(strtolower($extension), ['mp4', 'mov', 'avi']);
                                // Jika hanya satu file, tidak perlu menampilkan nomor file
                                $fileTitle = count($assets) > 1 ? 'File ' . ($index + 1) : 'Preview File';
                            @endphp

                            <div class="space-y-4 bg-gray-50 p-6 rounded-2xl">
                                <div class="flex items-center justify-between flex-wrap gap-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="p-2 bg-white rounded-lg shadow-sm">
                                            @if ($isVideo)
                                                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                            @else
                                                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                </svg>
                                            @endif
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-800">
                                            {{ $fileTitle }}
                                            <span
                                                class="text-sm font-normal text-gray-500">({{ strtoupper($extension) }})</span>
                                        </h3>
                                    </div>

                                    @if ($isVideo)
                                        <a href="{{ asset('storage/' . $asset) }}" download
                                            class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm hover:shadow group">
                                            <svg class="w-5 h-5 mr-2 transform group-hover:translate-y-0.5 transition-transform duration-200"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            Download {{ strtoupper($extension) }}
                                        </a>
                                    @endif
                                </div>

                                <!-- File Preview Container -->
                                <div class="relative rounded-xl overflow-hidden bg-white shadow-sm">
                                    @if ($isVideo)
                                        <!-- Video Player -->
                                        <video class="w-full h-[75vh]" controls controlsList="nodownload"
                                            preload="metadata">
                                            <source src="{{ asset('storage/' . $asset) }}"
                                                type="video/{{ $extension }}">
                                            Your browser does not support the video tag.
                                        </video>
                                    @else
                                        <!-- Loading Indicator for PDF -->
                                        <div class="absolute inset-0 flex items-center justify-center bg-white pdf-loading">
                                            <div class="flex flex-col items-center">
                                                <div
                                                    class="animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent">
                                                </div>
                                                <p class="mt-4 text-sm text-gray-500">Loading PDF...</p>
                                            </div>
                                        </div>

                                        <!-- PDF Viewer -->
                                        <div class="relative">
                                            <iframe src="{{ asset('storage/' . $asset) }}"
                                                class="w-full h-[75vh] transition-opacity duration-300"
                                                onload="this.style.opacity='1'; this.closest('.relative').previousElementSibling.style.display='none';"
                                                style="opacity: 0"></iframe>

                                            <!-- PDF Controls -->
                                            <button
                                                onclick="this.closest('.relative').querySelector('iframe').requestFullscreen()"
                                                class="absolute bottom-4 right-4 inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium text-gray-700 bg-white/90 hover:bg-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 shadow-sm hover:shadow backdrop-blur-sm">
                                                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                                </svg>
                                                Fullscreen
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .pdf-loading {
            z-index: 10;
            transition: opacity 0.3s ease-out;
            backdrop-filter: blur(4px);
        }

        iframe,
        video {
            background: #fff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle iframe load errors
            document.querySelectorAll('iframe').forEach(iframe => {
                iframe.onerror = function() {
                    iframe.parentElement.innerHTML = `
                        <div class="flex flex-col items-center justify-center p-8">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p class="mt-4 text-gray-600 text-center">Gagal memuat PDF. Silakan coba download file.</p>
                        </div>
                    `;
                };
            });

            // Handle video errors
            document.querySelectorAll('video').forEach(video => {
                video.onerror = function() {
                    video.parentElement.innerHTML = `
                        <div class="flex flex-col items-center justify-center p-8">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p class="mt-4 text-gray-600 text-center">Gagal memuat video. Silakan coba download file.</p>
                        </div>
                    `;
                };
            });
        });
    </script>
@endsection
