<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVideoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('update', $this->video);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "title" => "required|max:255",
            "description" => "nullable|string|max:255",
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
     public function messages(): array
    {
        return [
            "title.required" => "O Titulo precisa ser preenchido.",
            "title.max" => "O Titulo deve ter no máximo 255 caracteres.",
            "description.max" => "A descrição deve ter no máximo 255 caracteres.",
            ];
    }
}
