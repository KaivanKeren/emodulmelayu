<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaterialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'drive_urls' => ['nullable', 'array'],
            'drive_urls.*' => [
                'nullable',
                'url',
                'regex:/^https?:\/\/(?:www\.)?drive\.google\.com\/.+/i'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'drive_urls.*.url' => 'Please enter a valid URL',
            'drive_urls.*.regex' => 'Please enter a valid Google Drive URL',
        ];
    }
}