<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProcedureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'procedure_date' => ['sometimes', 'date'],
            'notes' => ['sometimes', 'string'],

            // If provided, replace items completely
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.item_name' => ['required_with:items', 'string', 'max:100'],
            'items.*.price' => ['required_with:items', 'numeric', 'min:0'],
        ];
    }
}
