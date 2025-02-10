@extends('layouts.admin')

@section('title', 'Edit Pertanyaan')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.16.4/katex.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill/dist/quill.snow.css">
    <link rel="stylesheet" href="/assets/quill.css">

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-lg rounded-2xl">
                <div class="p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Edit Pertanyaan Di {{ $question->assessment->title }}
                        </h2>
                        <a href="{{ route('assessments.show', $question->assessment) }}"
                            class="px-4 py-2 rounded-full text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            <i data-lucide="arrow-left" class="w-5 h-5 inline-block mr-1"></i>
                            Kembali
                        </a>
                    </div>

                    <form action="{{ route('questions.update', $question) }}" enctype="multipart/form-data" method="POST"
                        class="space-y-6">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="assessment_id" value="{{ $question->assessment_id }}">
                        <input type="hidden" name="question_text" id="question_text_input">

                        <!-- Question Text -->
                        <div class="rounded-md">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pertanyaan</label>
                            <div id="question_editor" class="bg-white">
                                {!! old('question_text', $question->content) !!}
                            </div>
                            @error('question_text')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Question Type -->
                        <div class="rounded-md">
                            <label for="question_type" class="block text-sm font-medium text-gray-700 mb-2">Tipe
                                Pertanyaan</label>
                            <div
                                class="flex items-center border border-gray-300 rounded-lg px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                <span class="text-gray-400">
                                    <i data-lucide="list" class="w-5 h-5"></i>
                                </span>
                                <select name="question_type" id="question_type"
                                    class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                                    <option value="single_choice"
                                        {{ $question->question_type === 'single_choice' ? 'selected' : '' }}>
                                        Pilihan Ganda</option>
                                    <option value="multiple_choice"
                                        {{ $question->question_type === 'multiple_choice' ? 'selected' : '' }}>
                                        Pilihan Ganda Kompleks</option>
                                </select>
                            </div>
                            @error('question_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Options Section -->
                        <div id="options_section" class="space-y-4">
                            <div class="flex justify-between items-center">
                                <label class="block text-sm font-medium text-gray-700">Pilihan Jawaban</label>
                                <button type="button" id="add_option"
                                    class="px-3 py-1.5 text-sm rounded-full text-orange-600 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                    <i data-lucide="plus" class="w-4 h-4 inline-block mr-1"></i>
                                    Tambah Pilihan
                                </button>
                            </div>

                            <div id="options_container" class="space-y-3">
                                @foreach ($question->options as $index => $option)
                                    <div class="option-group">
                                        <input type="hidden" name="options[]" class="option-content-input">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1">
                                                <div class="option-editor bg-white" data-index="{{ $index }}">
                                                    {!! $option->content !!}
                                                </div>
                                            </div>
                                            <div class="flex items-center answer-input">
                                                <input
                                                    type="{{ $question->question_type === 'single_choice' ? 'radio' : 'checkbox' }}"
                                                    name="{{ $question->question_type === 'single_choice' ? 'correct_answer' : 'correct_answer[' . $index . ']' }}"
                                                    value="{{ $index }}" {{ $option->is_correct ? 'checked' : '' }}
                                                    class="h-4 w-4 text-orange-500 focus:ring-orange-500 border-gray-300">
                                                <label class="ml-2 text-sm text-gray-700">Jawaban Benar</label>
                                            </div>
                                            <button type="button" onclick="removeOption(this)"
                                                class="text-gray-400 hover:text-red-500">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('assessments.show', $question->assessment) }}"
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.16.9/katex.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill-mathquill@1.0.2/dist/quill-mathquill.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill-image-resize-module@3.0.0/image-resize.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const optionsContainer = document.getElementById('options_container');
            const addOptionButton = document.getElementById('add_option');
            const questionType = document.getElementById('question_type');
            const form = document.querySelector('form');
            let optionCount = {{ count($question->options) }};
            let optionEditors = [];

            // Add error message elements
            const questionEditorContainer = document.getElementById('question_editor').parentElement;
            const questionErrorMsg = document.createElement('p');
            questionErrorMsg.className = 'quill-error text-red-500 text-sm mt-1';
            questionErrorMsg.textContent = 'Pertanyaan harus diisi.';
            questionErrorMsg.style.display = 'none';
            questionEditorContainer.appendChild(questionErrorMsg);

            // Initialize Quill for question text
            const questionEditor = new Quill('#question_editor', quillConfigs);
            const questionTextInput = document.getElementById('question_text_input');

            // Set initial content for question editor
            questionEditor.on('text-change', function() {
                questionTextInput.value = questionEditor.root.innerHTML.trim();
                validateQuestionContent();
            });
            questionTextInput.value = questionEditor.root.innerHTML.trim();

            // Validate question content
            function validateQuestionContent() {
                const content = questionEditor.getText().trim();
                questionErrorMsg.style.display = content === '' ? 'block' : 'none';
                return content !== '';
            }

            // Initialize Quill for existing options
            document.querySelectorAll('.option-editor').forEach((element, index) => {
                const editor = new Quill(element, quillConfigs);
                const hiddenInput = element.closest('.option-group').querySelector('.option-content-input');

                // Add error message for each option
                const errorMsg = document.createElement('p');
                errorMsg.className = 'quill-error text-red-500 text-sm mt-1';
                errorMsg.textContent = 'Jawaban harus diisi.';
                errorMsg.style.display = 'none';
                element.parentElement.appendChild(errorMsg);

                editor.on('text-change', function() {
                    hiddenInput.value = editor.root.innerHTML.trim();
                    validateOptionContent(editor, errorMsg);
                });
                hiddenInput.value = editor.root.innerHTML.trim();

                optionEditors.push({
                    editor,
                    errorMsg
                });
            });

            // Validate option content
            function validateOptionContent(editor, errorMsg) {
                const content = editor.getText().trim();
                errorMsg.style.display = content === '' ? 'block' : 'none';
                return content !== '';
            }

            // Function to update input types based on question type
            function updateAnswerInputs() {
                const inputType = questionType.value === 'single_choice' ? 'radio' : 'checkbox';
                const inputs = document.querySelectorAll('.answer-input input');
                inputs.forEach((input, index) => {
                    input.type = inputType;
                    if (inputType === 'checkbox') {
                        input.name = `correct_answer[${index}]`;
                    } else {
                        input.name = 'correct_answer';
                    }
                });
            }

            // Initial update
            updateAnswerInputs();

            // Update on question type change
            questionType.addEventListener('change', updateAnswerInputs);

            // Add new option with Quill editor
            addOptionButton.addEventListener('click', function() {
                const inputType = questionType.value === 'single_choice' ? 'radio' : 'checkbox';
                const optionDiv = document.createElement('div');
                optionDiv.className = 'option-group';
                optionDiv.innerHTML = `
            <input type="hidden" name="options[]" class="option-content-input">
            <div class="flex items-center gap-3">
                <div class="flex-1">
                    <div class="option-editor bg-white" data-index="${optionCount}"></div>
                </div>
                <div class="flex items-center answer-input">
                    <input type="${inputType}" 
                        name="${inputType === 'radio' ? 'correct_answer' : `correct_answer[${optionCount}]`}"
                        value="${optionCount}"
                        class="h-4 w-4 text-orange-500 focus:ring-orange-500 border-gray-300">
                    <label class="ml-2 text-sm text-gray-700">Jawaban Benar</label>
                </div>
                <button type="button" onclick="removeOption(this)" class="text-gray-400 hover:text-red-500">
                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                </button>
            </div>
        `;

                optionsContainer.appendChild(optionDiv);

                // Initialize Quill for new option
                const newEditorElement = optionDiv.querySelector('.option-editor');
                const newEditor = new Quill(newEditorElement, quillConfigs);
                const hiddenInput = optionDiv.querySelector('.option-content-input');

                // Add error message for new option
                const errorMsg = document.createElement('p');
                errorMsg.className = 'quill-error text-red-500 text-sm mt-1';
                errorMsg.textContent = 'Jawaban harus diisi.';
                errorMsg.style.display = 'none';
                newEditorElement.parentElement.appendChild(errorMsg);

                newEditor.on('text-change', function() {
                    hiddenInput.value = newEditor.root.innerHTML.trim();
                    validateOptionContent(newEditor, errorMsg);
                });

                optionEditors.push({
                    editor: newEditor,
                    errorMsg
                });
                optionCount++;
                lucide.createIcons();
            });

            // Form submission validation
            form.addEventListener('submit', function(e) {
                let isValid = true;

                // Validate question
                if (!validateQuestionContent()) {
                    isValid = false;
                }

                // Validate all options
                optionEditors.forEach(({
                    editor,
                    errorMsg
                }) => {
                    if (!validateOptionContent(editor, errorMsg)) {
                        isValid = false;
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                }
            });
        });

        function removeOption(button) {
            const optionGroup = button.closest('.option-group');
            if (optionGroup) {
                // Remove the option group
                optionGroup.remove();

                // Update all remaining option indices
                const options = document.querySelectorAll('.option-group');
                options.forEach((option, index) => {
                    const input = option.querySelector('.answer-input input');
                    if (input) {
                        input.value = index;
                        if (input.type === 'checkbox') {
                            input.name = `correct_answer[${index}]`;
                        }
                    }
                });
            }
        }

        // Quill configuration
        const quillConfigs = {
            modules: {
                toolbar: [
                    ["bold", "italic", "underline", "strike"],
                    [{
                        header: [1, 2, 3, 4, 5, 6, false]
                    }],
                    [{
                        list: "ordered"
                    }, {
                        list: "bullet"
                    }],
                    [{
                        script: "sub"
                    }, {
                        script: "super"
                    }],
                    [{
                        indent: "-1"
                    }, {
                        indent: "+1"
                    }],
                    [{
                        direction: "rtl"
                    }],
                    [{
                        font: []
                    }],
                    [{
                        align: []
                    }],
                    ["link", "image", "formula"],
                    ["clean"],
                ]
            },
            imageResize: {
                modules: ["Resize", "DisplaySize", "Toolbar"],
            },
            placeholder: 'Tulis teks...',
            theme: 'snow'
        };
    </script>
@endsection
