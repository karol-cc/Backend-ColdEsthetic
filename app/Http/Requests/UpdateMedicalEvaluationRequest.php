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
            'weight' => ['required', 'numeric', 'min:1'],
            'height' => ['required', 'numeric', 'gt:0'],

            'bmi' => ['required', 'numeric', 'gt:0'],
            'bmi_status' => ['required', 'string', 'max:50'],

            'medical_background' => ['required', 'string'],
        ];
    }
}
