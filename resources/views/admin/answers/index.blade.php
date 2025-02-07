@extends('layouts.admin')

@section('title', 'Jawaban Siswa | ' . $assessment->title)

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
            <h1 class="text-2xl font-semibold text-gray-900">
                Hasil Penilaian: {{ $assessment->title }}
            </h1>
            <p class="mt-2 text-sm text-gray-600">
                Total Pertanyaan: {{ $totalQuestions }}
            </p>

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
@endsection
