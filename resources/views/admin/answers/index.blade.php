@extends('layouts.admin')

@section('title', 'Jawaban Siswa | ' . $assessment->title)

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">
                        Hasil Penilaian: {{ $assessment->title }}
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Total Pertanyaan: {{ $totalQuestions }}
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Countdown and Refresh Button -->
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Refresh dalam: <span id="countdown"
                                class="font-medium">30</span>s</span>
                        <button onclick="manualRefresh()"
                            class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Refresh
                        </button>
                    </div>
                    <!-- Export Button -->
                    <a href="{{ route('answers.export', ['assessment' => $assessment->id]) }}"
                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export PDF
                    </a>
                </div>
            </div>

            <div class="mt-8">
                <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Siswa
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Asal Sekolah
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Skor
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Soal Dijawab
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Penyelesaian
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($respondents as $respondent)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $respondent['user']->name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $respondent['school'] }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900">
                                            {{ number_format($respondent['total_score'], 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $respondent['answered_questions'] }} / {{ $totalQuestions }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full"
                                                style="width: {{ $respondent['completion_percentage'] }}%"></div>
                                        </div>
                                        <span
                                            class="text-sm text-gray-500">{{ $respondent['completion_percentage'] }}%</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <a href="{{ route('answers.detail', ['assessment' => $assessment->id, 'user' => $respondent['user']->id]) }}"
                                            class="text-blue-500 hover:text-blue-700 text-sm font-medium">
                                            Lihat Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($respondents->isEmpty())
                    <div class="text-center text-gray-500 py-6">
                        <p>Belum ada siswa yang mengerjakan penilaian ini.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let countdownTime = 30;
            let countdownInterval;

            function startCountdown() {
                countdownTime = 30;
                document.getElementById('countdown').textContent = countdownTime;

                clearInterval(countdownInterval);
                countdownInterval = setInterval(() => {
                    countdownTime--;
                    document.getElementById('countdown').textContent = countdownTime;

                    if (countdownTime <= 0) {
                        window.location.reload();
                    }
                }, 1000);
            }

            function manualRefresh() {
                window.location.reload();
            }

            // Start the countdown when the page loads
            document.addEventListener('DOMContentLoaded', startCountdown);
        </script>
    @endpush
@endsection
