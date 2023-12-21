<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuestionsUpdateRequest extends FormRequest
{
    protected $types = ['text', 'select', 'checkbox', 'radio'];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            '*.survey_id' => [Rule::exists('surveys', 'id')],
            '*.type' => ['bail','required', Rule::in($this->types)],
            '*.question' => ['bail','required', 'string', 'max:255'],
            '*.description' => ['bail','nullable', 'string', 'max:1000'],
            '*.data' => ['bail','nullable'],
        ];
    }
}
