<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ubah jika ada logika otorisasi khusus
    }

    public function rules(): array
    {
        return [
            'title' => 'required|max:255',
            'category' => 'required|max:255',
            'status' => 'required|in:Belum Terbuka,Terbuka,Terjawab,Selesai',
            'timer' => 'nullable',
            'questions' => 'required|array|min:1',
            'questions.*.content' => 'required|string',
            'questions.*.question_type' => 'required|in:single_choice,multiple_choice',
            'questions.*.options' => 'required|array|min:1',
            'questions.*.options.*' => 'required|string',
            'questions.*.correct_answer' => 'required_if:questions.*.question_type,single_choice',
            'questions.*.correct_answer.*' => 'required_if:questions.*.question_type,multiple_choice',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul assessment wajib diisi',
            'title.max' => 'Judul tidak boleh lebih dari 255 karakter',
            'category.required' => 'Kategori assessment wajib diisi',
            'status.required' => 'Status assessment wajib diisi',
            'status.in' => 'Status yang dipilih tidak valid',
            'questions.required' => 'Assessment harus memiliki minimal satu pertanyaan',
            'questions.*.content.required' => 'Teks pertanyaan wajib diisi',
            'questions.*.question_type.required' => 'Tipe pertanyaan wajib dipilih',
            'questions.*.question_type.in' => 'Tipe pertanyaan tidak valid',
            'questions.*.options.required' => 'Setiap pertanyaan harus memiliki pilihan jawaban',
            'questions.*.options.min' => 'Setiap pertanyaan harus memiliki minimal satu pilihan jawaban',
            'questions.*.options.*.required' => 'Teks pilihan jawaban wajib diisi',
            'questions.*.correct_answer.required_if' => 'Pilihan jawaban benar wajib dipilih untuk pertanyaan pilihan ganda',
            'questions.*.correct_answer.*.required_if' => 'Pilihan jawaban benar wajib dipilih untuk pertanyaan pilihan ganda kompleks',
        ];
    }
}
