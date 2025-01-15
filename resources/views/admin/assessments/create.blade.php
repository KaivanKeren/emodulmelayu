@extends('layouts.admin')

@section('title', 'Create Assessment')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-lg rounded-2xl">
                <div class="p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Buat Assessment Baru</h2>
                    </div>

                    <form action="{{ route('assessments.store') }}" method="POST" class="space-y-8">
                        @csrf
                        
                        <!-- Assessment Details -->
                        <div class="space-y-6">
                            <!-- Title -->
                            <div class="rounded-md">
                                <div class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                    <span class="text-gray-400">
                                        <i data-lucide="file-text" class="w-5 h-5"></i>
                                    </span>
                                    <input type="text" name="title" id="title" placeholder="Judul"
                                        value="{{ old('title') }}"
                                        class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                                </div>
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Category -->
                            <div class="rounded-md">
                                <div class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                    <span class="text-gray-400">
                                        <i data-lucide="tag" class="w-5 h-5"></i>
                                    </span>
                                    <input type="text" name="category" id="category" placeholder="Kategori"
                                        value="{{ old('category') }}"
                                        class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                                </div>
                                @error('category')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="rounded-md">
                                <div class="flex items-center border border-gray-300 rounded-full px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                    <span class="text-gray-400">
                                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                                    </span>
                                    <select name="status" id="status"
                                        class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                                        <option value="belum terbuka">Belum Terbuka</option>
                                        <option value="terbuka">Terbuka</option>
                                        <option value="terjawab">Terjawab</option>
                                        <option value="selesai">Selesai</option>
                                    </select>
                                </div>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Questions Section -->
                        <div class="space-y-6">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900">Pertanyaan</h3>
                                <button type="button" id="add_question"
                                    class="px-4 py-2 rounded-full text-orange-600 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                    <i data-lucide="plus" class="w-5 h-5 inline-block mr-1"></i>
                                    Tambah Pertanyaan
                                </button>
                            </div>

                            <div id="questions_container" class="space-y-8">
                                <!-- Questions will be added here -->
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('assessments.index') }}"
                                class="px-6 py-2.5 rounded-full text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                Batal
                            </a>
                            <button type="submit"
                                class="px-6 py-2.5 rounded-full text-white bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                Buat Assessment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const questionsContainer = document.getElementById('questions_container');
            const addQuestionButton = document.getElementById('add_question');
            let questionCount = 0;

            function createOptionHTML(questionIndex, optionIndex, type) {
                return `
                    <div class="option-group">
                        <div class="flex items-center gap-3">
                            <div class="flex-1">
                                <div class="flex items-center border border-gray-300 rounded-lg px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                    <span class="text-gray-400">
                                        <i data-lucide="circle" class="w-5 h-5"></i>
                                    </span>
                                    <input type="text" name="questions[${questionIndex}][options][]" 
                                        placeholder="Masukkan pilihan jawaban"
                                        class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                                </div>
                            </div>
                            <div class="flex items-center">
                                <input type="${type}" 
                                    name="questions[${questionIndex}][correct_answer]${type === 'checkbox' ? '[]' : ''}" 
                                    value="${optionIndex}"
                                    class="h-4 w-4 text-orange-500 focus:ring-orange-500 border-gray-300">
                                <label class="ml-2 text-sm text-gray-700">Jawaban Benar</label>
                            </div>
                            <button type="button" onclick="removeOption(this)" 
                                class="text-gray-400 hover:text-red-500">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                `;
            }

            function createQuestionHTML(questionIndex) {
                return `
                    <div class="question-block border border-gray-200 rounded-xl p-6 space-y-6">
                        <div class="flex justify-between items-center">
                            <h4 class="text-lg font-medium text-gray-900">Pertanyaan ${questionIndex + 1}</h4>
                            <button type="button" onclick="removeQuestion(this)" 
                                class="text-gray-400 hover:text-red-500">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <!-- Question Text -->
                        <div class="rounded-md">
                            <div class="flex items-center border border-gray-300 rounded-lg px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                <span class="text-gray-400">
                                    <i data-lucide="help-circle" class="w-5 h-5"></i>
                                </span>
                                <input type="text" name="questions[${questionIndex}][content]"
                                    placeholder="Masukkan pertanyaan"
                                    class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                            </div>
                        </div>

                        <!-- Question Type -->
                        <div class="rounded-md">
                            <div class="flex items-center border border-gray-300 rounded-lg px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                <span class="text-gray-400">
                                    <i data-lucide="list" class="w-5 h-5"></i>
                                </span>
                                <select name="questions[${questionIndex}][question_type]"
                                    onchange="updateQuestionType(this, ${questionIndex})"
                                    class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                                    <option value="single_choice">Pilihan Ganda</option>
                                    <option value="multiple_choice">Pilihan Ganda Kompleks</option>
                                </select>
                            </div>
                        </div>

                        <!-- Options Section -->
                        <div class="options-section space-y-4">
                            <div class="flex justify-between items-center">
                                <label class="block text-sm font-medium text-gray-700">Pilihan Jawaban</label>
                                <button type="button" onclick="addOption(this, ${questionIndex})"
                                    class="px-3 py-1.5 text-sm rounded-full text-orange-600 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                    <i data-lucide="plus" class="w-4 h-4 inline-block mr-1"></i>
                                    Tambah Pilihan
                                </button>
                            </div>
                            <div class="options-container space-y-3">
                                ${createOptionHTML(questionIndex, 0, 'radio')}
                            </div>
                        </div>
                    </div>
                `;
            }

            addQuestionButton.addEventListener('click', function() {
                const newQuestion = createQuestionHTML(questionCount);
                questionsContainer.insertAdjacentHTML('beforeend', newQuestion);
                questionCount++;
                lucide.createIcons();
            });

            // Add first question automatically
            addQuestionButton.click();
        });

        function removeQuestion(button) {
            button.closest('.question-block').remove();
        }

        function removeOption(button) {
            button.closest('.option-group').remove();
        }

        function addOption(button, questionIndex) {
            const optionsContainer = button.closest('.options-section').querySelector('.options-container');
            const questionType = button.closest('.question-block').querySelector('select[name*="question_type"]').value;
            const type = questionType === 'single_choice' ? 'radio' : 'checkbox';
            const optionIndex = optionsContainer.children.length;
            
            const newOption = createOptionHTML(questionIndex, optionIndex, type);
            optionsContainer.insertAdjacentHTML('beforeend', newOption);
            lucide.createIcons();
        }

        function updateQuestionType(select, questionIndex) {
            const optionsContainer = select.closest('.question-block').querySelector('.options-container');
            const type = select.value === 'single_choice' ? 'radio' : 'checkbox';
            
            const inputs = optionsContainer.querySelectorAll('input[type="radio"], input[type="checkbox"]');
            inputs.forEach((input, index) => {
                input.type = type;
                input.name = `questions[${questionIndex}][correct_answer]${type === 'checkbox' ? '[]' : ''}`;
                input.value = index;
            });
        }

        function createOptionHTML(questionIndex, optionIndex, type) {
            return `
                <div class="option-group">
                    <div class="flex items-center gap-3">
                        <div class="flex-1">
                            <div class="flex items-center border border-gray-300 rounded-lg px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                <span class="text-gray-400">
                                    <i data-lucide="circle" class="w-5 h-5"></i>
                                </span>
                                <input type="text" name="questions[${questionIndex}][options][]" 
                                    placeholder="Masukkan pilihan jawaban"
                                    class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                            </div>
                        </div>
                        <div class="flex items-center">
                            <input type="${type}" 
                                name="questions[${questionIndex}][correct_answer]${type === 'checkbox' ? '[]' : ''}" 
                                value="${optionIndex}"
                                class="h-4 w-4 text-orange-500 focus:ring-orange-500 border-gray-300">
                            <label class="ml-2 text-sm text-gray-700">Jawaban Benar</label>
                        </div>
                        <button type="button" onclick="removeOption(this)" 
                            class="text-gray-400 hover:text-red-500">
                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
            `;
        }
    </script>
@endsection