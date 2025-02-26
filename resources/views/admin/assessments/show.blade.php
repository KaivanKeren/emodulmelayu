@extends('layouts.admin')

@section('title', $assessment->title)

@section('content')
    <link rel="stylesheet" href="/assets/quill.css">

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Detail Assessment</h1>
                    <p class="mt-1 text-sm text-gray-500">Kelola dan tinjau detail assessment</p>
                </div>
                <a href="{{ route('assessments.index') }}"
                    class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 shadow-sm transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                    Kembali
                </a>
            </div>

            <!-- Main Content Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-6 space-y-8">
                    {{-- Assessment Basic Info --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Left Column --}}
                        <div class="space-y-6">
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</label>
                                <p class="mt-1 text-lg font-medium text-gray-900">{{ $assessment->title }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</label>
                                <p class="mt-1 text-lg font-medium text-gray-900">{{ $assessment->category }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu
                                    Pengerjaan</label>
                                @if ($assessment->timer === null)
                                    <p class="mt-1 text-lg font-medium text-gray-900">Tidak ada batas waktu</p>
                                @else
                                    <p class="mt-1 text-lg font-medium text-gray-900">
                                        @php
                                            try {
                                                $time = new DateTime($assessment->timer);
                                                $hours = $time->format('H');
                                                $minutes = $time->format('i');
                                                $seconds = $time->format('s');

                                                $output = [];
                                                if ($hours > 0) {
                                                    $output[] = $hours . ' jam';
                                                }
                                                if ($minutes > 0) {
                                                    $output[] = $minutes . ' menit';
                                                }
                                                if ($seconds > 0) {
                                                    $output[] = $seconds . ' detik';
                                                }

                                                echo empty($output) ? '0 detik' : implode(' ', $output);
                                            } catch (Exception $e) {
                                                echo 'Waktu tidak valid';
                                            }
                                        @endphp
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Right Column --}}
                        <div class="space-y-6">
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Status</label>
                                <div class="mt-1">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                        @if ($assessment->status === 'Selesai') bg-green-100 text-green-800
                                        @elseif($assessment->status === 'Terjawab') bg-blue-100 text-blue-800
                                        @elseif($assessment->status === 'Terbuka') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($assessment->status) }}
                                    </span>
                                </div>
                            </div>

                            @if ($assessment->status === 'Terbuka')
                                <div class="space-y-4">
                                    <div>
                                        <label
                                            class="text-xs font-medium text-gray-500 uppercase tracking-wider">Token</label>
                                        <div class="mt-2">
                                            @if ($assessment->token && $assessment->token_expires_at && now()->lt($assessment->token_expires_at))
                                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 relative">
                                                    <div
                                                        class="font-mono text-lg text-gray-900 flex items-center justify-between">
                                                        <span id="assessmentToken">{{ $assessment->token }}</span>
                                                        <button onclick="copyToken()"
                                                            class="text-gray-500 hover:text-gray-700 focus:outline-none transition-colors"
                                                            title="Salin Token">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-2">
                                                        Berlaku hingga pukul:
                                                        {{ $assessment->token_expires_at->setTimezone('Asia/Jakarta')->format('H:i') }}
                                                        ({{ $assessment->token_expires_at->setTimezone('Asia/Jakarta')->diffForHumans() }})
                                                    </div>
                                                    <div id="copySuccessMessage"
                                                        class="hidden absolute top-full left-0 mt-1 bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
                                                        Token berhasil disalin
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-500">Token tidak tersedia atau sudah
                                                    kedaluwarsa</span>
                                            @endif
                                        </div>
                                    </div>

                                    <form action="{{ route('assessments.regenerate-token', $assessment) }}" method="POST"
                                        class="mt-4">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 shadow-sm transition-colors">
                                            <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
                                            Generate Token Baru
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions Section -->
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">Pertanyaan</h2>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <i data-lucide="circle-help" class="h-5 w-5 text-gray-400"></i>
                            <p class="text-sm text-gray-600">Total: <span
                                    class="font-medium">{{ $assessment->questions->count() }}</span> pertanyaan</p>
                        </div>
                        @if ($respondentCount > 0)
                            <span class="text-gray-300">|</span>
                            <div class="flex items-center gap-2">
                                <i data-lucide="users" class="h-5 w-5 text-green-400"></i>
                                <a href="{{ route('answers.show', $assessment->id) }}"
                                    class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1 hover:cursor-pointer">
                                    <span class="font-medium">{{ $respondentCount }}</span> siswa sudah menjawab
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
                <a href="{{ route('questions.create', ['assessment' => $assessment->id]) }}"
                    class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 shadow-sm transition-colors">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                    Tambah Pertanyaan
                </a>
            </div>

            <div class="space-y-4">
                <button onclick="toggleAllCorrectAnswers()"
                    class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 shadow-sm transition-colors">
                    <i data-lucide="eye" class="w-4 h-4 mr-2 show-icon"></i>
                    <i data-lucide="eye-off" class="w-4 h-4 mr-2 hide-icon hidden"></i>
                    Tampilkan Semua Jawaban
                </button>
                @forelse ($assessment->questions as $index => $question)
                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-100 hover:border-orange-200 transition-colors">
                        <div class="flex items-start gap-4">
                            <div
                                class="flex-shrink-0 mr-3 w-8 h-8 flex items-center justify-center rounded-full bg-orange-100 text-orange-600 font-semibold text-sm">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="space-y-2">
                                        <!-- Question content using Quill viewer -->
                                        <h3 class="text-lg font-medium text-gray-900">
                                            <div class="ql-viewer">{!! $question->content !!}</div>
                                        </h3>
                                        <button id="toggle-btn-{{ $question->id }}"
                                            onclick="toggleCorrectAnswers({{ $question->id }})"
                                            class="inline-flex items-center px-3 py-1 text-sm text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                            <i data-lucide="eye" class="w-4 h-4 mr-2 show-icon"></i>
                                            <i data-lucide="eye-off" class="w-4 h-4 mr-2 hide-icon hidden"></i>
                                            <span class="toggle-text">Tampilkan Jawaban</span>
                                        </button>
                                    </div>
                                    <span class="text-xs text-gray-500 ml-4">
                                        @if ($question->question_type === 'multiple_choice')
                                            * Jawaban lebih dari satu
                                        @elseif ($question->question_type === 'single_choice')
                                            * Pilih salah satu
                                        @endif
                                    </span>
                                </div>
                                <div class="space-y-3 ml-4">
                                    @foreach ($question->options as $optionIndex => $option)
                                        <div class="flex items-start gap-3 text-gray-700">
                                            <span
                                                class="text-sm text-gray-500 w-6 mt-1">{{ chr(65 + $optionIndex) }}.</span>
                                            <div class="flex items-start gap-4 flex-1">
                                                <!-- Option content using Quill viewer -->
                                                <div class="ql-viewer">{!! $option->content !!}</div>
                                                @if ($option->is_correct)
                                                    <i data-lucide="check-circle"
                                                        class="w-5 h-5 text-green-500 hidden correct-icon-{{ $question->id }} flex-shrink-0"></i>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('questions.edit', $question) }}"
                                    class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                    <i data-lucide="edit" class="w-5 h-5"></i>
                                </a>
                                <form action="{{ route('questions.destroy', $question) }}" method="POST"
                                    class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Are you sure you want to delete this question?')"
                                        class="p-2 rounded-lg text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <p class="text-gray-500">Belum ada pertanyaan yang ditambahkan</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="p-6 bg-gray-50 border-t border-gray-100 rounded-b-xl">
            <div class="flex justify-end gap-4">
                <a href="{{ route('assessments.edit', $assessment) }}"
                    class="inline-flex items-center px-6 py-2.5 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-colors">
                    <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                    Edit Assessment
                </a>
                <form action="{{ route('assessments.destroy', $assessment) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Are you sure you want to delete this assessment?')"
                        class="inline-flex items-center px-6 py-2.5 rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 shadow-sm transition-colors">
                        <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i>
                        Hapus Assessment
                    </button>
                </form>
            </div>
        </div>
    </div>
    </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all toggle states to hidden
            let toggleStates = {};
            let allQuestionsToggleState = false;

            const viewers = document.querySelectorAll('.ql-viewer');

            viewers.forEach(viewer => {
                // Find all links in the viewer
                const links = viewer.querySelectorAll('a[href*="drive.google.com/file"]');

                links.forEach(link => {
                    const url = link.getAttribute('href');

                    // Check if it's a Google Drive file link
                    if (url.includes('drive.google.com/file')) {
                        try {
                            // Extract the file ID from the Google Drive URL
                            const fileIdMatch = url.match(/\/d\/([^\/]+)/);

                            if (fileIdMatch && fileIdMatch[1]) {
                                const fileId = fileIdMatch[1];

                                const thumbnailUrl =
                                    `https://drive.google.com/thumbnail?id=${fileId}&sz=w1000`;

                                // Create an image with the thumbnail URL
                                const img = document.createElement('img');
                                img.src = thumbnailUrl;
                                img.alt = 'Google Drive Image';
                                img.className = 'rounded-lg max-w-full my-2';
                                img.style.maxHeight = '400px';

                                // For higher quality on click, keep the original link
                                link.innerHTML = '';
                                link.appendChild(img);

                                // Optional: Add a preview icon to indicate it's clickable
                                const iconOverlay = document.createElement('div');
                                iconOverlay.className =
                                    'absolute top-0 right-0 bg-gray-800 bg-opacity-50 p-1 rounded-bl-lg';
                                iconOverlay.innerHTML =
                                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"></path><path d="M10 14L21 3"></path></svg>';

                                // Make the link position relative for the overlay
                                link.style.position = 'relative';
                                link.style.display = 'inline-block';
                                // link.appendChild(iconOverlay);
                            }
                        } catch (error) {
                            console.error('Error processing Google Drive link:', error);
                        }
                    }
                });
            });

            window.toggleCorrectAnswers = function(questionId) {
                const correctIcons = document.querySelectorAll(`.correct-icon-${questionId}`);
                const toggleButton = document.querySelector(`#toggle-btn-${questionId}`);

                // Initialize if not exists
                if (toggleStates[questionId] === undefined) {
                    toggleStates[questionId] = false;
                }

                // Toggle state
                toggleStates[questionId] = !toggleStates[questionId];

                // Update visibility and button text
                correctIcons.forEach(icon => {
                    icon.classList.toggle('hidden', !toggleStates[questionId]);
                });

                // Update button icon and text
                if (toggleStates[questionId]) {
                    toggleButton.querySelector('.show-icon').classList.add('hidden');
                    toggleButton.querySelector('.hide-icon').classList.remove('hidden');
                    toggleButton.querySelector('.toggle-text').textContent = 'Sembunyikan Jawaban';
                } else {
                    toggleButton.querySelector('.show-icon').classList.remove('hidden');
                    toggleButton.querySelector('.hide-icon').classList.add('hidden');
                    toggleButton.querySelector('.toggle-text').textContent = 'Tampilkan Jawaban';
                }
            };


            // Function to toggle correct answers for a specific question
            window.toggleAllCorrectAnswers = function() {
                // Get all question IDs
                const questionIds = @json($assessment->questions->pluck('id'));
                const allToggleButton = document.querySelector('button[onclick="toggleAllCorrectAnswers()"]');

                // Toggle the state for all questions
                allQuestionsToggleState = !allQuestionsToggleState;

                questionIds.forEach(questionId => {
                    const correctIcons = document.querySelectorAll(`.correct-icon-${questionId}`);
                    const toggleButton = document.querySelector(`#toggle-btn-${questionId}`);

                    // Update correct answer icons
                    correctIcons.forEach(icon => {
                        icon.classList.toggle('hidden', !allQuestionsToggleState);
                    });

                    // Update toggle button
                    if (allQuestionsToggleState) {
                        toggleButton.querySelector('.show-icon').classList.add('hidden');
                        toggleButton.querySelector('.hide-icon').classList.remove('hidden');
                        toggleButton.querySelector('.toggle-text').textContent = 'Sembunyikan Jawaban';
                    } else {
                        toggleButton.querySelector('.show-icon').classList.remove('hidden');
                        toggleButton.querySelector('.hide-icon').classList.add('hidden');
                        toggleButton.querySelector('.toggle-text').textContent = 'Tampilkan Jawaban';
                    }

                    // Update toggle states
                    toggleStates[questionId] = allQuestionsToggleState;
                });

                // Update the icon and text of the "Tampilkan Semua Jawaban Benar" button
                if (allQuestionsToggleState) {
                    allToggleButton.querySelector('.show-icon').classList.add('hidden');
                    allToggleButton.querySelector('.hide-icon').classList.remove('hidden');
                    allToggleButton.querySelector('span').textContent = 'Sembunyikan Semua Jawaban Benar';
                } else {
                    allToggleButton.querySelector('.show-icon').classList.remove('hidden');
                    allToggleButton.querySelector('.hide-icon').classList.add('hidden');
                    allToggleButton.querySelector('span').textContent = 'Tampilkan Semua Jawaban Benar';
                }
            };
        });

        function copyToken() {
            // Get the token element
            const tokenElement = document.getElementById('assessmentToken');

            // Create a temporary textarea to copy the text
            const tempTextArea = document.createElement('textarea');
            tempTextArea.value = tokenElement.textContent;
            document.body.appendChild(tempTextArea);

            // Select and copy the text
            tempTextArea.select();
            document.execCommand('copy');

            // Remove the temporary textarea
            document.body.removeChild(tempTextArea);

            // Show success message
            const successMessage = document.getElementById('copySuccessMessage');
            successMessage.classList.remove('hidden');

            // Hide success message after 2 seconds
            setTimeout(() => {
                successMessage.classList.add('hidden');
            }, 2000);
        }
    </script>

@endsection
