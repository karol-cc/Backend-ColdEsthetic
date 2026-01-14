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
            'procedure_id' => ['required', 'integer', 'exists:procedures,id', 'unique:medical_evaluations,procedure_id'],
            'patient_id' => ['required', 'integer', 'exists:patients,id'],

            // We store this as JSON (text column) but accept array for flexibility
            'evaluation_data' => ['required', 'array'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
