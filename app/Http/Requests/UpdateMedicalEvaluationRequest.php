<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicalEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'evaluation_data' => ['sometimes', 'array'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
