<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by auth:sanctum middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'referrer_name' => 'sometimes|string|in:Dra. Adele,Dra. Fernanda,Dr. Alexander',
            'appointment_datetime' => 'sometimes|date',
            'duration_minutes' => 'sometimes|integer|min:15|max:240',
            'planned_procedures' => 'sometimes|array|min:1',
            'planned_procedures.*.name' => 'required_with:planned_procedures|string',
            'notes' => 'nullable|string|max:1000',
            'status' => 'sometimes|string|in:pending,confirmed,completed,cancelled'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'referrer_name.in' => 'El doctor seleccionado no es válido',
            'appointment_datetime.date' => 'La fecha debe ser válida',
            'duration_minutes.min' => 'La duración mínima es 15 minutos',
            'duration_minutes.max' => 'La duración máxima es 240 minutos',
            'planned_procedures.min' => 'Debe seleccionar al menos un procedimiento',
            'notes.max' => 'Las notas no pueden exceder 1000 caracteres',
            'status.in' => 'El estado seleccionado no es válido'
        ];
    }
}
