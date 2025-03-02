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

                    <form action="{{ route('teacher.assessments.update', $assessment->id) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
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


                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('teacherDashboard') }}"
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
    @endsection
