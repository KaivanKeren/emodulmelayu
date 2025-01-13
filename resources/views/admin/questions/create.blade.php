@extends('layouts.admin')

@section('title', 'Tambah Pertanyaan')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-lg rounded-2xl">
                <div class="p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Tambah Pertanyaan Baru Di {{ $assessment->title }}
                        </h2>
                        <a href="{{ route('assessments.show', $assessment) }}"
                            class="px-4 py-2 rounded-full text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            <i data-lucide="arrow-left" class="w-5 h-5 inline-block mr-1"></i>
                            Kembali
                        </a>
                    </div>

                    <form action="{{ route('questions.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="assessment_id" value="{{ $assessment->id }}">

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
                                    placeholder="Masukkan pertanyaan" value="{{ old('question_text') }}"
                                    class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
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
                                    <option value="single_choice">Pilihan Ganda</option>
                                    <option value="multiple_choice">Pilihan Ganda Kompleks</option>
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
                                <!-- Initial Option Fields -->
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
                                                    class="block w-full pl-2 bg-transparent border-0 focus:ring-0 focus:outline-none">
                                            </div>
                                        </div>
                                        <div class="flex items-center answer-input">
                                            <input type="radio" name="correct_answer[]" value="0"
                                                class="h-4 w-4 text-orange-500 focus:ring-orange-500 border-gray-300">
                                            <label class="ml-2 text-sm text-gray-700">Jawaban Benar</label>
                                        </div>
                                        <button type="button" onclick="removeOption(this)"
                                            class="text-gray-400 hover:text-red-500">
                                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('assessments.show', $assessment) }}"
                                class="px-6 py-2.5 rounded-full text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                Batal
                            </a>
                            <button type="submit"
                                class="px-6 py-2.5 rounded-full text-white bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                Simpan Pertanyaan
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
                let optionCount = 1;

                // Function to update input types based on question type
                function updateAnswerInputs() {
                    const inputType = questionType.value === 'single_choice' ? 'radio' : 'checkbox';
                    const inputs = document.querySelectorAll('.answer-input input');
                    inputs.forEach(input => {
                        input.type = inputType;
                        if (inputType === 'checkbox') {
                            input.name = `correct_answer[${input.value}]`;
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
