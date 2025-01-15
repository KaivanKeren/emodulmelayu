@extends('layouts.admin')

@section('title', 'Tambah Pertanyaan')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-lg rounded-2xl">
                <div class="p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Tambah Pertanyaan Baru Di {{ $assessment->title }}</h2>
                        <div class="flex gap-3">
                            <button type="button" id="add_question"
                                class="px-4 py-2 rounded-full text-orange-600 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                <i data-lucide="plus" class="w-5 h-5 inline-block mr-1"></i>
                                Tambah Pertanyaan
                            </button>
                            <a href="{{ route('assessments.show', $assessment) }}"
                                class="px-4 py-2 rounded-full text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                <i data-lucide="arrow-left" class="w-5 h-5 inline-block mr-1"></i>
                                Kembali
                            </a>
                        </div>
                    </div>

                    <form action="{{ route('questions.store') }}" method="POST" class="space-y-8">
                        @csrf
                        <input type="hidden" name="assessment_id" value="{{ $assessment->id }}">

                        <!-- Questions Container -->
                        <div id="questions_container" class="space-y-8">
                            <!-- Question template will be added here -->
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('assessments.show', $assessment) }}"
                                class="px-6 py-2.5 rounded-full text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                Batal
                            </a>
                            <button type="submit"
                                class="px-6 py-2.5 rounded-full text-white bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                Simpan Semua Pertanyaan
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

            function createQuestionBlock() {
                const questionBlock = document.createElement('div');
                questionBlock.className = 'question-block border border-gray-200 rounded-xl p-6 space-y-6';

                questionBlock.innerHTML = `
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Pertanyaan ${questionCount + 1}</h3>
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
                            <input type="text" name="questions[${questionCount}][question_text]"
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
                            <select name="questions[${questionCount}][question_type]"
                                onchange="updateQuestionType(this, ${questionCount})"
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
                            <button type="button" onclick="addOption(this, ${questionCount})"
                                class="px-3 py-1.5 text-sm rounded-full text-orange-600 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                <i data-lucide="plus" class="w-4 h-4 inline-block mr-1"></i>
                                Tambah Pilihan
                            </button>
                        </div>
                        <div class="options-container space-y-3">
                            ${createOptionHTML(questionCount, 0, 'radio')}
                        </div>
                    </div>
                `;

                return questionBlock;
            }

            addQuestionButton.addEventListener('click', function() {
                const newQuestion = createQuestionBlock();
                questionsContainer.appendChild(newQuestion);
                questionCount++;
                lucide.createIcons();
                renumberQuestions();
            });

            // Add first question automatically
            addQuestionButton.click();
        });

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

        function addOption(button, questionIndex) {
            const optionsContainer = button.closest('.options-section').querySelector('.options-container');
            const questionType = button.closest('.question-block').querySelector('select[name*="question_type"]').value;
            const type = questionType === 'single_choice' ? 'radio' : 'checkbox';
            const optionIndex = optionsContainer.children.length;

            const newOption = createOptionHTML(questionIndex, optionIndex, type);
            optionsContainer.insertAdjacentHTML('beforeend', newOption);
            lucide.createIcons();
        }

        function removeOption(button) {
            const optionGroup = button.closest('.option-group');
            const optionsContainer = optionGroup.closest('.options-container');

            if (optionsContainer.children.length > 1) {
                optionGroup.remove();
                updateOptionIndexes(optionsContainer);
            } else {
                alert('Pertanyaan harus memiliki minimal satu pilihan jawaban');
            }
        }

        function removeQuestion(button) {
            const questionBlock = button.closest('.question-block');
            const questionsContainer = questionBlock.parentElement;

            if (questionsContainer.children.length > 1) {
                questionBlock.remove();
                renumberQuestions();
            } else {
                alert('Assessment harus memiliki minimal satu pertanyaan');
            }
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

        function renumberQuestions() {
            const questions = document.querySelectorAll('.question-block');
            questions.forEach((question, index) => {
                const heading = question.querySelector('h3');
                heading.textContent = `Pertanyaan ${index + 1}`;

                // Update all input names with new index
                updateQuestionInputs(question, index);
            });
        }

        function updateQuestionInputs(questionBlock, newIndex) {
            // Update question text input
            const questionText = questionBlock.querySelector('input[name*="[question_text]"]');
            questionText.name = `questions[${newIndex}][question_text]`;

            // Update question type select
            const questionType = questionBlock.querySelector('select[name*="[question_type]"]');
            questionType.name = `questions[${newIndex}][question_type]`;
            questionType.setAttribute('onchange', `updateQuestionType(this, ${newIndex})`);

            // Update option inputs
            const optionsContainer = questionBlock.querySelector('.options-container');
            updateOptionIndexes(optionsContainer, newIndex);

            // Update add option button
            const addOptionBtn = questionBlock.querySelector('.options-section button');
            addOptionBtn.setAttribute('onclick', `addOption(this, ${newIndex})`);
        }

        function updateOptionIndexes(optionsContainer, questionIndex) {
            const options = optionsContainer.querySelectorAll('.option-group');
            const type = optionsContainer.querySelector('input[type="radio"], input[type="checkbox"]').type;

            options.forEach((option, index) => {
                const optionInput = option.querySelector('input[name*="[options]"]');
                const correctInput = option.querySelector(`input[type="${type}"]`);

                if (questionIndex !== undefined) {
                    optionInput.name = `questions[${questionIndex}][options][]`;
                    correctInput.name =
                        `questions[${questionIndex}][correct_answer]${type === 'checkbox' ? '[]' : ''}`;
                }
                correctInput.value = index;
            });
        }
    </script>
@endsection
