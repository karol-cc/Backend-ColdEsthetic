<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'referrer_name' => trim((string) $this->input('referrer_name', '')),
        ]);

        $fullName = trim((string) $this->input('full_name', ''));

        if ($fullName !== '' && (!$this->has('first_name') || !$this->has('last_name'))) {
            $parts = preg_split('/\s+/', $fullName) ?: [];
            $first = $parts[0] ?? '';
            $last = implode(' ', array_slice($parts, 1));

            $this->merge([
                'first_name' => $this->input('first_name', $first),
                'last_name' => $this->input('last_name', $last !== '' ? $last : $first),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            // Remitente (texto obligatorio)
            'referrer_name' => ['required', 'string', 'max:50'],

            // Datos del paciente
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'cellphone' => ['required', 'string', 'max:15'],
            'age' => ['required', 'integer', 'min:0', 'max:120'],
            'biological_sex' => ['required', 'string', 'in:Femenino,Masculino,Otro'],
        ];
    }
}
