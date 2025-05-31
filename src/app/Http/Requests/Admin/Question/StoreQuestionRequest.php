<?php

namespace App\Http\Requests\Admin\Question;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole(['manager', 'general']);
    }

    public function rules()
    {
        return [
            'question_text' => ['required', 'string', 'min:10'],
            'choices' => [
                'required',
                'array',
                'size:4',
                function ($attribute, $value, $fail) {
                    if (!is_array($value)) {
                        return; // Do not proceed if not an array, previous rule handles it.
                    }
                    if (count(array_unique($value)) !== count($value)) {
                        $fail('All choices must be unique.');
                    }
                },
            ],
            'choices.*' => ['required', 'string', 'min:2', 'max:255'],
            'correct_choice' => ['required', 'integer', 'min:1', 'max:4'],
            'explanation' => ['nullable', 'string', 'min:10'],
            'difficulty_level' => ['required', 'string', 'in:easy,medium,hard'],
            'category' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'question_text.required' => 'The question text is required.',
            'question_text.min' => 'The question text must be at least 10 characters.',
            'choices.required' => 'You must provide 4 choices for the question.',
            'choices.size' => 'You must provide exactly 4 choices for the question.',
            'choices.*.required' => 'Each choice is required.',
            'choices.*.min' => 'Each choice must be at least 2 characters.',
            'choices.*.max' => 'Each choice must not exceed 255 characters.',
            'correct_choice.required' => 'You must select the correct answer.',
            'correct_choice.min' => 'The correct choice must be between 1 and 4.',
            'correct_choice.max' => 'The correct choice must be between 1 and 4.',
            'explanation.min' => 'The explanation must be at least 10 characters if provided.',
            'difficulty_level.required' => 'Please select a difficulty level.',
            'difficulty_level.in' => 'The selected difficulty level is invalid.',
        ];
    }
} 