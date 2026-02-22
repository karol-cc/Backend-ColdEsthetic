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
            'weight' => ['sometimes', 'numeric', 'min:1'],
            'height' => ['sometimes', 'numeric', 'gt:0'],
            'medical_background' => ['sometimes', 'string'],
        ];
    }
}
