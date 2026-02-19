<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProcedureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Relación correcta
            'medical_evaluation_id' => [
                'required',
                'integer',
                'exists:medical_evaluations,id',
                'unique:procedures,medical_evaluation_id'
            ],

            // Datos del procedimiento
            'procedure_date' => ['required', 'date'],
            'notes' => ['required', 'string'],

            // Items / tratamientos
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:100'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
