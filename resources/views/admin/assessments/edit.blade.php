@extends('layouts.admin')

@section('title', 'Edit Assessment')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-lg rounded-2xl">
                <div class="p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Edit Assessment</h2>
                    </div>

                    <form action="{{ route('assessments.update', $assessment->id) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        <!-- Title -->
                        <div class="rounded-md">
                            <div
                                class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                <span class="text-gray-400">
                                    <i data-lucide="file-text" class="w-5 h-5"></i>
                                </span>
                                <input type="text" required name="title" id="title" placeholder="Judul"
                                    value="{{ old('title', $assessment->title) }}"
                                    class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                            </div>
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div class="rounded-md">
                            <div
                                class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                <span class="text-gray-400">
                                    <i data-lucide="tag" class="w-5 h-5"></i>
                                </span>
                                <input type="text" required name="category" id="category" placeholder="Kategori"
                                    value="{{ old('category', $assessment->category) }}"
                                    class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                            </div>
                            @error('category')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="rounded-md">
                            <div
                                class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                <span class="text-gray-400">
                                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                                </span>
                                <select name="status" id="status"
                                    class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                                    <option value="Belum Terbuka"
                                        {{ old('status', $assessment->status) == 'Belum Terbuka' ? 'selected' : '' }}>
                                        Belum Terbuka</option>
                                    <option value="Terbuka"
                                        {{ old('status', $assessment->status) == 'Terbuka' ? 'selected' : '' }}>Terbuka
                                    </option>
                                </select>
                            </div>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Timer --}}
                        <div class="rounded-md">
                            <div
                                class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                <span class="text-gray-400">
                                    <i data-lucide="clock" class="w-5 h-5"></i>
                                </span>
                                <input type="time" name="timer" id="timer" step="1" required
                                    value="{{ old('timer', $assessment->timer) }}"
                                    class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none"
                                    placeholder="00:00:00">
                                <input type="hidden" id="timer_format" name="timer_format" value="24">
                            </div>
                            @error('timer')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('assessments.index') }}"
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

        <style>
            /* Force 24-hour time format across devices */
            input[type="time"]::-webkit-datetime-edit-ampm-field {
                display: none;
            }

            /* Additional CSS to ensure consistent display */
            input[type="time"] {
                font-family: monospace;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Get the time input element
                var timeInput = document.getElementById('timer');

                // Set the pattern attribute for 24-hour format
                timeInput.setAttribute('pattern', '[0-9]{2}:[0-9]{2}:[0-9]{2}');

                // Force 24-hour display when user interacts with the input
                timeInput.addEventListener('click', function() {
                    if (!this.value) {
                        this.value = '00:00:00';
                    }
                });

                // Format the time to ensure it's in 24-hour format
                timeInput.addEventListener('change', function() {
                    // Ensure the value is in HH:MM:SS format
                    let timeValue = this.value;

                    // If seconds are not specified, add them
                    if (timeValue.split(':').length < 3) {
                        timeValue += ':00';
                        this.value = timeValue;
                    }
                });
            });
        </script>
    @endsection
