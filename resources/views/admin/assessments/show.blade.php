@extends('layouts.admin')

@section('title', 'Detail Assessment')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-lg rounded-2xl">
                <div class="p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Detail Assessment</h2>
                        <a href="{{ route('assessments.index') }}"
                            class="px-4 py-2 rounded-full text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            <i data-lucide="arrow-left" class="w-5 h-5 inline-block mr-1"></i>
                            Kembali
                        </a>
                    </div>

                    <div class="space-y-6">
                        <!-- Assessment Details -->
                        <div class="border-b border-gray-200 pb-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Judul</h3>
                                    <p class="mt-1 text-lg text-gray-900">{{ $assessment->title }}</p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Kategori</h3>
                                    <p class="mt-1 text-lg text-gray-900">{{ $assessment->category }}</p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Status</h3>
                                    <span
                                        class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                        @if ($assessment->status === 'Selesai') bg-green-100 text-green-800
                                        @elseif($assessment->status === 'Terjawab') bg-blue-100 text-blue-800
                                        @elseif($assessment->status === 'Terbuka') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($assessment->status) }}
                                    </span>
                                </div>

                                @if ($assessment->status === 'Terbuka')
                                    <div class="col-span-2">
                                        <h3 class="text-sm font-medium text-gray-500">Token</h3>
                                        <div class="mt-2 flex items-center space-x-4">
                                            @if ($assessment->token && $assessment->token_expires_at && now()->lt($assessment->token_expires_at))
                                                <div class="flex items-center space-x-2">
                                                    <span
                                                        class="text-lg font-mono bg-gray-100 px-4 py-2 rounded-lg">{{ $assessment->token }}</span>
                                                    <span class="text-sm text-gray-500">
                                                        Berlaku hingga: {{ $assessment->token_expires_at->format('H:i') }}
                                                        ({{ $assessment->token_expires_at->diffForHumans() }})
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-500">Token tidak tersedia atau sudah
                                                    kedaluwarsa</span>
                                            @endif

                                            <form action="{{ route('assessments.regenerate-token', $assessment) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="px-4 py-2 rounded-full text-white bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                                    <i data-lucide="refresh-cw" class="w-4 h-4 inline-block mr-1"></i>
                                                    Generate Token Baru
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Questions Section Header -->
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold text-gray-900">Pertanyaan</h3>
                            <a href="{{ route('questions.create', ['assessment' => $assessment->id]) }}"
                                class="px-4 py-2 rounded-full text-white bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                <i data-lucide="plus" class="w-5 h-5 inline-block mr-1"></i>
                                Tambah Pertanyaan
                            </a>
                        </div>

                        <!-- Questions -->
                        <div>
                            @forelse ($assessment->questions as $question)
                                <div class="bg-gray-50 rounded-lg p-6 mb-4">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex justify-between items-center">
                                                <h4 class="text-lg font-medium text-gray-900 mb-4">{{ $question->content }}
                                                </h4>
                                                <span class="text-sm text-gray-500">*
                                                    @if ($question->question_type === 'multiple_choice')
                                                        Jawaban lebih dari satu.
                                                    @elseif ($question->question_type === 'single_choice')
                                                        Pilih salah satu.
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="ml-4 space-y-2">
                                                @foreach ($question->options as $option)
                                                    <div class="flex items-center">
                                                        <i data-lucide="circle" class="w-4 h-4 text-gray-400 mr-2"></i>
                                                        <span>{{ $option->content }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="ml-4 flex space-x-2">
                                            <a href="{{ route('questions.edit', $question) }}"
                                                class="px-3 py-1.5 rounded-full text-blue-600 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                <i data-lucide="edit" class="w-4 h-4"></i>
                                            </a>
                                            <form action="{{ route('questions.destroy', $question) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    onclick="return confirm('Are you sure you want to delete this question?')"
                                                    class="px-3 py-1.5 rounded-full text-red-600 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 text-center py-4">Tidak ada pertanyaan untuk assessment ini.</p>
                            @endforelse
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('assessments.edit', $assessment) }}"
                                class="px-6 py-2.5 rounded-full text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i data-lucide="edit" class="w-5 h-5 inline-block mr-1"></i>
                                Edit Assessment
                            </a>
                            <form action="{{ route('assessments.destroy', $assessment) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    onclick="return confirm('Are you sure you want to delete this assessment?')"
                                    class="px-6 py-2.5 rounded-full text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <i data-lucide="trash-2" class="w-5 h-5 inline-block mr-1"></i>
                                    Hapus Assessment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
