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
                                <p class="mt-2 text-sm text-gray-500">
                                    Dibuat oleh {{ $material->user->name }} • {{ $material->created_at->format('d M Y') }}
                                </p>
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
                            $assets = json_decode($material->assets, true) ?? [];
                            $assets = is_array($assets) ? $assets : [$assets];
                        @endphp

                        @foreach ($assets as $index => $asset)
                            @php
                                $isGoogleDrive = str_contains($asset, 'drive.google.com');
                                $fileTitle = count($assets) > 1 ? 'File ' . ($index + 1) : 'File';

                                if ($isGoogleDrive) {
                                    preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $asset, $matches);
                                    $fileId = $matches[1] ?? null;
                                    $downloadUrl = "https://drive.google.com/uc?export=download&id={$fileId}";
                                    $originalUrl = $asset;
                                } else {
                                    $downloadUrl = $asset;
                                    $originalUrl = $asset;
                                }
                            @endphp

                            <div class="space-y-4 bg-gray-50 p-6 rounded-2xl" x-data="{ loading: true }">
                                <div class="flex items-center justify-between flex-wrap gap-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="p-2 bg-white rounded-lg shadow-sm">
                                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-800">
                                            {{ $fileTitle }}
                                            @if (!$isGoogleDrive && isset($extension))
                                                <span
                                                    class="text-sm font-normal text-gray-500">({{ strtoupper($extension) }})</span>
                                            @endif
                                        </h3>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <a href="{{ $downloadUrl }}" download
                                            class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium text-green-600 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 shadow-sm hover:shadow">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            Download
                                        </a>
                                    </div>
                                </div>

                                <!-- File Preview Container -->
                                <div class="relative rounded-xl overflow-hidden bg-white shadow-sm" x-data="{ loading: true }">
                                    @if ($isGoogleDrive && $fileId)
                                        <iframe src="https://drive.google.com/file/d/{{ $fileId }}/preview"
                                            class="w-full h-[75vh]" x-on:load="setTimeout(() => loading = false, 1000)"
                                            allowfullscreen allow="autoplay"></iframe>
                                    @else
                                        <div class="w-full h-[75vh] flex items-center justify-center">
                                            <a href="{{ $asset }}" target="_blank" rel="noopener noreferrer"
                                                class="flex flex-col items-center p-8 text-gray-500 hover:text-gray-700">
                                                <svg class="w-16 h-16" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span class="mt-4 text-sm font-medium">Click to open file</span>
                                            </a>
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
        iframe {
            background: #fff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>

    <script>
        async function downloadFile(url) {
            try {
                const response = await fetch(url);
                const blob = await response.blob();
                const filename = url.split('/').pop(); // Get filename from URL

                // Create a temporary link to trigger download
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = filename;

                // Append to document, click, and remove
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                // Clean up the URL object
                window.URL.revokeObjectURL(link.href);
            } catch (error) {
                console.error('Download failed:', error);
                alert('Failed to download file. Please try again.');
            }
        }
    </script>
@endsection
