@extends('layouts.admin')

@section('title', 'Detail Material')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <!-- Header Section -->
                <div class="border-b border-gray-200">
                    <div class="p-6 sm:px-8">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $material->title }}</h2>
                            <a href="{{ route('materials.index') }}"
                                class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Kembali
                            </a>
                        </div>
                        <p class="mt-4 text-gray-600 text-lg leading-relaxed">{{ $material->description }}</p>
                    </div>
                </div>

                <!-- PDF Viewer Section -->
                <div class="p-6 sm:p-8">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">Preview File PDF</h3>
                            <!-- Download Button -->
                            <a href="{{ asset('storage/' . $material->asset) }}" download
                                class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download PDF
                            </a>
                        </div>

                        <!-- PDF Viewer Container -->
                        <div class="relative rounded-xl overflow-hidden bg-gray-50">
                            <!-- Loading Indicator -->
                            <div class="absolute inset-0 flex items-center justify-center bg-gray-50 pdf-loading">
                                <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent"></div>
                            </div>
                            
                            <!-- PDF Viewer -->
                            <iframe 
                                src="{{ asset('storage/' . $material->asset) }}"
                                class="w-full h-[75vh] transition-opacity duration-300"
                                onload="this.style.opacity='1'; this.previousElementSibling.style.display='none';"
                                style="opacity: 0"
                            ></iframe>
                        </div>

                        <!-- PDF Controls -->
                        <div class="flex items-center justify-end space-x-4 mt-4">
                            <button onclick="document.querySelector('iframe').requestFullscreen()"
                                class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                                </svg>
                                Fullscreen
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .pdf-loading {
            z-index: 10;
            transition: opacity 0.3s ease-out;
        }
        
        iframe {
            background: #fff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const iframe = document.querySelector('iframe');
            
            // Handle iframe load error
            iframe.onerror = function() {
                iframe.parentElement.innerHTML = `
                    <div class="flex flex-col items-center justify-center p-8">
                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p class="mt-4 text-gray-600 text-center">Failed to load PDF. Please try downloading the file instead.</p>
                    </div>
                `;
            };
        });
    </script>
@endsection