@extends('layouts.admin')

@section('title', 'Tambah Pertanyaan')

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
                        <h2 class="text-2xl font-bold text-gray-900">Tambah Pertanyaan Baru Di {{ $assessment->title }}</h2>
                        <div class="flex gap-3">

                            <a href="{{ route('assessments.show', $assessment) }}"
                                class="px-4 py-2 rounded-full text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                <i data-lucide="arrow-left" class="w-5 h-5 inline-block mr-1"></i>
                                Kembali
                            </a>
                        </div>
                    </div>

                    <form action="{{ route('questions.store') }}" method="POST" class="space-y-8"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="assessment_id" value="{{ $assessment->id }}">

                        <!-- Questions Container -->
                        <div id="questions_container" class="space-y-8">
                            <!-- Question template will be added here -->
                        </div>
                        <div class="space-y-6">
                            <div class="flex justify-end items-center">
                                <button type="button" id="add_question"
                                    class="px-4 py-2 rounded-full text-orange-600 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                    <i data-lucide="plus" class="w-5 h-5 inline-block mr-1"></i>
                                    Tambah Pertanyaan
                                </button>
                            </div>
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

    <script src="/assets/create-question.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.16.9/katex.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill-mathquill@1.0.2/dist/quill-mathquill.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill-image-resize-module@3.0.0/image-resize.min.js"></script>
@endsection
