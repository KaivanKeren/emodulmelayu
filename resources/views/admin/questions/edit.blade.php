@extends('layouts.admin')

@section('title', 'Edit Pertanyaan')

@section('content')
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

                        <!-- Question Text -->
                        <div class="rounded-md">
                            <label for="question_text"
                                class="block text-sm font-medium text-gray-700 mb-2">Pertanyaan</label>
                            <div
                                class="flex items-center border border-gray-300 rounded-lg px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                <span class="text-gray-400">
                                    <i data-lucide="help-circle" class="w-5 h-5"></i>
                                </span>
                                <input type="text" name="question_text" id="question_text"
                                    placeholder="Masukkan pertanyaan" value="{{ old('question_text', $question->content) }}"
                                    class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                            </div>
                            @error('question_text')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="rounded-md">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gambar Pertanyaan (Opsional)</label>
                            <div class="relative">
                                <!-- Image Preview Container -->
                                @if ($question->image)
                                    <div id="image-preview" class="mb-3">
                                        <div class="relative inline-block group">
                                            <img src="{{ Storage::url($question->image) }}" alt="Gambar Pertanyaan"
                                                class="max-h-48 rounded-lg border border-gray-200 object-cover">
                                            <button type="button" id="remove-image"
                                                class="absolute -top-2 -right-2 bg-white rounded-full p-1.5 shadow-md border border-gray-200 text-gray-400 hover:text-red-500 transition-colors">
                                                <i data-lucide="x" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                <!-- File Upload Area -->
                                <div
                                    class="border-2 border-dashed border-gray-300 rounded-lg p-6 transition-all duration-200 ease-in-out">
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <span class="text-gray-400">
                                            <i data-lucide="image-plus" class="w-10 h-10"></i>
                                        </span>
                                        <div class="text-center space-y-2">
                                            <label class="relative cursor-pointer">
                                                <span class="text-orange-600 hover:text-orange-700 text-sm font-medium">
                                                    {{ $question->image ? 'Ganti' : 'Klik untuk upload' }}
                                                </span>
                                                <span class="text-gray-500 text-sm"> atau drag and drop</span>
                                                <input type="file" name="image" accept="image/*"
                                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                                    onchange="handleImageUpload(this)">
                                            </label>
                                            <p class="text-xs text-gray-500">PNG, JPG, GIF (Maks. 2MB)</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hidden input to track image removal -->
                                <input type="hidden" name="remove_image" id="remove_image_input" value="0">
                            </div>
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
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1">
                                                <div
                                                    class="flex items-center border border-gray-300 rounded-lg px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                                    <span class="text-gray-400">
                                                        <i data-lucide="circle" class="w-5 h-5"></i>
                                                    </span>
                                                    <input type="text" name="options[]"
                                                        placeholder="Masukkan pilihan jawaban"
                                                        value="{{ $option->content }}"
                                                        class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                                                </div>
                                            </div>
                                            <div class="flex items-center answer-input">
                                                <input
                                                    type="{{ $question->question_type === 'single_choice' ? 'radio' : 'checkbox' }}"
                                                    name="{{ $question->question_type === 'single_choice' ? 'correct_answer' : 'correct_answer[' . $index . ']' }}"
                                                    value="{{ $index }}"
                                                    {{ $option->is_correct ? 'checked' : '' }}
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const optionsContainer = document.getElementById('options_container');
            const addOptionButton = document.getElementById('add_option');
            const questionType = document.getElementById('question_type');
            const removeImageButton = document.getElementById('remove-image');
            const removeImageInput = document.getElementById('remove_image_input');
            const imageUploadContainer = document.querySelector('.border-dashed');
            const fileInput = document.querySelector('input[name="image"]');
            const imagePreview = document.getElementById('image-preview');

            let optionCount = {{ count($question->options) }};

            function handleImageRemoval() {
                if (imagePreview) {
                    imagePreview.style.display = 'none';
                    removeImageInput.value = '1';
                    fileInput.value = '';

                    if (imageUploadContainer) {
                        imageUploadContainer.classList.remove('hidden');
                    }
                }
            }

            // Event listener for remove image button
            if (removeImageButton) {
                removeImageButton.addEventListener('click', handleImageRemoval);
            }


            @if ($question->image)
                const uploadContainer = document.querySelector('.border-dashed');
                removeImageButton?.addEventListener('click', function() {
                    imagePreview.style.display = 'none';
                    removeImageInput.value = '1';
                    fileInput.value = '';
                });
                uploadContainer.classList.add('hidden');

                document.getElementById('remove-image')?.addEventListener('click', function() {
                    const uploadContainer = document.querySelector('.border-dashed');

                    document.getElementById('image-preview').style.display = 'none';
                    removeImageInput.value = '1';
                    fileInput.value = '';
                    uploadContainer.classList.remove('hidden');
                });
            @endif

            window.handleImageUpload = function(input) {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        // Check if image preview already exists
                        if (imagePreview) {
                            const img = imagePreview.querySelector('img');
                            img.src = e.target.result;
                            imagePreview.style.display = 'block';

                            if (imageUploadContainer) {
                                imageUploadContainer.classList.add('hidden');
                            }
                        } else {
                            // Create new preview if it doesn't exist
                            const previewHtml = `
                    <div id="image-preview" class="mb-3">
                        <div class="relative inline-block group">
                            <img src="${e.target.result}" alt="Gambar Pertanyaan"
                                 class="max-h-48 rounded-lg border border-gray-200 object-cover">
                            <button type="button" id="remove-image" 
                                    class="absolute -top-2 -right-2 bg-white rounded-full p-1.5 shadow-md border border-gray-200 text-gray-400 hover:text-red-500 transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    `;
                            input.closest('.rounded-md').insertAdjacentHTML('afterbegin', previewHtml);
                        }

                        // Reset removal input
                        removeImageInput.value = '0';

                        // Rebind remove image button
                        const newRemoveButton = document.getElementById('remove-image');
                        if (newRemoveButton) {
                            newRemoveButton.addEventListener('click', handleImageRemoval);
                        }
                    };

                    reader.readAsDataURL(input.files[0]);
                }
            }

            // Attach handleImageUpload to file input
            fileInput.addEventListener('change', function() {
                handleImageUpload(this);
            });


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

            addOptionButton.addEventListener('click', function() {
                const inputType = questionType.value === 'single_choice' ? 'radio' : 'checkbox';
                const newOption = `
                    <div class="option-group">
                        <div class="flex items-center gap-3">
                            <div class="flex-1">
                                <div class="flex items-center border border-gray-300 rounded-lg px-4 py-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
                                    <span class="text-gray-400">
                                        <i data-lucide="circle" class="w-5 h-5"></i>
                                    </span>
                                    <input type="text" name="options[]" 
                                        placeholder="Masukkan pilihan jawaban"
                                        class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                                </div>
                            </div>
                            <div class="flex items-center answer-input">
                                <input type="${inputType}" name="${inputType === 'radio' ? 'correct_answer' : `correct_answer[${optionCount}]`}" 
                                    value="${optionCount}"
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
                optionsContainer.insertAdjacentHTML('beforeend', newOption);
                optionCount++;
                lucide.createIcons();
            });
        });

        function removeOption(button) {
            button.closest('.option-group').remove();
        }
    </script>
@endsection
