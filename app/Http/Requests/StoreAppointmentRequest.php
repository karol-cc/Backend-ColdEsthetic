<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
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
            'patient_id' => 'required|integer|exists:patients,id',
            'referrer_name' => 'required|string|in:Dra. Adele,Dra. Fernanda,Dr. Alexander',
            'appointment_datetime' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15|max:240',
            'planned_procedures' => 'required|array|min:1',
            'planned_procedures.*.name' => 'required|string',
            'notes' => 'nullable|string|max:1000'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'patient_id.required' => 'Debe seleccionar un paciente',
            'patient_id.exists' => 'El paciente seleccionado no existe',
            'referrer_name.required' => 'Debe seleccionar un doctor',
            'referrer_name.in' => 'El doctor seleccionado no es válido',
            'appointment_datetime.required' => 'Debe seleccionar una fecha y hora',
            'appointment_datetime.after' => 'La cita debe ser en una fecha futura',
            'duration_minutes.required' => 'Debe especificar la duración',
            'duration_minutes.min' => 'La duración mínima es 15 minutos',
            'duration_minutes.max' => 'La duración máxima es 240 minutos',
            'planned_procedures.required' => 'Debe seleccionar al menos un procedimiento',
            'planned_procedures.min' => 'Debe seleccionar al menos un procedimiento',
            'notes.max' => 'Las notas no pueden exceder 1000 caracteres'
        ];
    }
}
