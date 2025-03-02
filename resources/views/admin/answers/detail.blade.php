@extends('layouts.admin')

@section('content')
    <link rel="stylesheet" href="/assets/quill.css">

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $assessment->title ?? 'Assessment Details' }}</h1>
                        <p class="mt-1 text-sm text-gray-500">Detail jawaban untuk {{ $user->name ?? 'User' }}</p>
                    </div>
                    <a href="{{ route('answers.show', $assessment) }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali
                    </a>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Assessment</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm text-blue-600 font-medium">Total Nilai</p>
                        <p class="text-2xl font-bold text-blue-800">{{ $summary['total_score'] ?? 0 }}%</p>
                    </div>
                    <div class="p-4 bg-green-50 rounded-lg">
                        <p class="text-sm text-green-600 font-medium">Penyelesaian</p>
                        <p class="text-2xl font-bold text-green-800">{{ $summary['completion_percentage'] ?? 0 }}%</p>
                    </div>
                    <div class="p-4 bg-purple-50 rounded-lg">
                        <p class="text-sm text-purple-600 font-medium">Pertanyaan Dijawab</p>
                        <p class="text-2xl font-bold text-purple-800">
                            {{ $summary['answered_questions'] ?? 0 }}/{{ $summary['total_questions'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Detailed Answers -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Detail Jawaban</h2>

                @if (isset($questionsDetail) && count($questionsDetail) > 0)
                    <div class="space-y-6">
                        @foreach ($questionsDetail as $index => $detail)
                            <div
                                class="border rounded-lg p-4 {{ $detail['is_answered'] ? 'border-gray-200' : 'border-red-200 bg-red-50' }}">
                                <!-- Question Header -->
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <span
                                            class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none {{ $detail['is_answered'] ? 'text-green-800 bg-green-100' : 'text-red-800 bg-red-100' }} rounded">
                                            Pertanyaan {{ $index + 1 }}
                                        </span>
                                        <h3 class="ql-viewer mt-2 text-lg font-medium text-gray-900">{!! $detail['question']['content'] ?? 'No content' !!}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Tipe Soal: @if (isset($detail['question']['type']) && $detail['question']['type'] === 'multiple_choice')
                                                Pilihan Ganda Kompleks
                                            @elseif (isset($detail['question']['type']) && $detail['question']['type'] === 'single_choice')
                                                Pilihan Ganda
                                            @else
                                                Tidak Diketahui
                                            @endif
                                        </p>
                                    </div>
                                    @if ($detail['is_answered'] && isset($detail['user_answers']['score']))
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $detail['user_answers']['score'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            Score: {{ $detail['user_answers']['score'] }}%
                                        </span>
                                    @endif
                                </div>

                                <!-- Options -->
                                @if (isset($detail['all_options']) && count($detail['all_options']) > 0)
                                    <div class="mt-4 space-y-2">
                                        @foreach ($detail['all_options'] as $option)
                                            <div
                                                class="flex items-center p-3 rounded-lg {{ $detail['is_answered'] &&
                                                isset($detail['user_answers']['selected_options']) &&
                                                collect($detail['user_answers']['selected_options'])->contains('option_id', $option['id'])
                                                    ? ($option['is_correct']
                                                        ? 'bg-green-50 border border-green-200'
                                                        : 'bg-red-50 border border-red-200')
                                                    : ($option['is_correct']
                                                        ? 'bg-gray-50 border border-gray-200'
                                                        : 'bg-white border border-gray-200') }}">

                                                <!-- Option Indicator -->
                                                @if ($detail['is_answered'] && isset($detail['user_answers']['selected_options']))
                                                    @if (collect($detail['user_answers']['selected_options'])->contains('option_id', $option['id']))
                                                        <svg class="h-5 w-5 {{ $option['is_correct'] ? 'text-green-500' : 'text-red-500' }} mr-3"
                                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="{{ $option['is_correct'] ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12' }}" />
                                                        </svg>
                                                    @else
                                                        <svg class="h-5 w-5 text-gray-400 mr-3" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <circle cx="12" cy="12" r="10" stroke-width="2" />
                                                        </svg>
                                                    @endif
                                                @endif

                                                <!-- Option Text -->
                                                <span
                                                    class="ql-viewer text-sm {{ isset($option['is_correct']) && $option['is_correct'] ? 'font-medium' : 'text-gray-700' }}">
                                                    {!! $option['content'] ?? 'No content' !!}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="mt-4 text-sm text-gray-500">No options available for this question.</div>
                                @endif

                                <!-- Submission Time -->
                                @if ($detail['is_answered'] && isset($detail['user_answers']['submitted_at']))
                                    <div class="mt-4 text-sm text-gray-500">
                                        Submitted:
                                        {{ \Carbon\Carbon::parse($detail['user_answers']['submitted_at'])->format('M d, Y H:i:s') }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <p>No questions found for this assessment.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .fade-in {
                animation: fadeIn 0.3s ease-out forwards;
            }
        </style>
    @endpush
@endsection
