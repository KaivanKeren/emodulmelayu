@extends('layouts.admin')

@section('title', 'Detail Assessment')

@section('content')
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
                <!-- Assessment Info Section -->
                <div class="p-6 border-b border-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</label>
                                <p class="mt-1 text-lg font-medium text-gray-900">{{ $assessment->title }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</label>
                                <p class="mt-1 text-lg font-medium text-gray-900">{{ $assessment->category }}</p>
                            </div>
                        </div>
                        <div class="space-y-4">
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
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Token</label>
                                    <div class="mt-2 flex items-center gap-4">
                                        @if ($assessment->token && $assessment->token_expires_at && now()->lt($assessment->token_expires_at))
                                            <div class="flex-1 bg-gray-50 rounded-lg p-3 border border-gray-200 relative">
                                                <div
                                                    class="font-mono text-lg text-gray-900 flex items-center justify-between">
                                                    <span id="assessmentToken">{{ $assessment->token }}</span>
                                                    <button onclick="copyToken()"
                                                        class="ml-2 text-gray-500 hover:text-gray-700 focus:outline-none"
                                                        title="Salin Token">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round"
                                                                strokeWidth={2}
                                                                d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Berlaku hingga pukul:
                                                    {{ $assessment->token_expires_at->setTimezone('Asia/Jakarta')->format('H:i') }}
                                                    ({{ $assessment->token_expires_at->setTimezone('Asia/Jakarta')->diffForHumans() }})
                                                </div>

                                                <!-- Optional: Add a hidden success message -->
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
                                <form action="{{ route('assessments.regenerate-token', $assessment) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 shadow-sm transition-colors">
                                        <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
                                        Generate Token Baru
                                    </button>
                                </form>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Questions Section -->
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <div class="space-y-1">
                    <h2 class="text-xl font-semibold text-gray-900">Pertanyaan</h2>
                    <div class="flex items-center gap-4">
                        <p class="text-sm text-gray-500">Total: {{ $assessment->questions->count() }} pertanyaan</p>
                        <span class="text-gray-300">|</span>
                        <p class="text-sm text-gray-500">
                            <i data-lucide="users" class="w-4 h-4 inline-block mr-1"></i>
                            {{ $respondents }} siswa sudah menjawab
                        </p>
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
                                class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-orange-100 text-orange-600 font-semibold text-sm">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="space-y-2">
                                        <h3 class="text-lg font-medium text-gray-900">{{ $question->content }}</h3>
                                        @if ($question->image)
                                            <div class="mt-3 mb-4">
                                                <img src="{{ asset('storage/' . $question->image) }}" alt="Question image"
                                                    alt="Question image" class="rounded-lg max-w-md h-auto shadow-sm">
                                            </div>
                                        @endif
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
                                        <div class="flex items-center gap-3 text-gray-700">
                                            <span class="text-sm text-gray-500 w-6">{{ chr(65 + $optionIndex) }}.</span>
                                            <span class="flex items-center gap-2">
                                                {{ $option->content }}
                                                @if ($option->is_correct)
                                                    <i data-lucide="check-circle"
                                                        class="w-5 h-5 text-green-500 hidden correct-icon-{{ $question->id }}"></i>
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('questions.edit', $question) }}"
                                    class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                    <i data-lucide="edit" class="w-5 h-5"></i>
                                </a>
                                <form action="{{ route('questions.destroy', $question) }}" method="POST" class="inline">
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
                    <div class="text-center py-12 bg-gray-50 rounded-lg border border-gray-100">
                        <i data-lucide="clipboard-list" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                        <p class="text-gray-500">Tidak ada pertanyaan untuk assessment ini.</p>
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
