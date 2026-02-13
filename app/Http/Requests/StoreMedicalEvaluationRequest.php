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
            'weight' => ['required', 'numeric', 'min:2', 'max:400'],
            'height' => ['required', 'numeric', 'between:1.2,2.5'],

            // Antecedentes médicos
            'medical_background' => ['required', 'string'],
        ];
    }
}
