<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Relación obligatoria
            'patient_id' => ['required', 'integer', 'exists:patients,id'],

            // Datos clínicos
            'weight' => ['required', 'numeric', 'min:1'],
            'height' => ['required', 'numeric', 'gt:0'],

            // Antecedentes médicos
            'medical_background' => ['nullable', 'string'],
        ];
    }
}
