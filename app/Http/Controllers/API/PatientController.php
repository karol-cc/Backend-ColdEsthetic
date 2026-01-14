<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientRequest;
use App\Models\Patient;

class PatientController extends Controller
{
    public function store(StorePatientRequest $request)
    {
        $data = $request->validated();

        $weight = (float) $data['weight'];
        $height = (float) $data['height'];
        $bmi = null;

        if ($height > 0) {
            $bmi = $weight / ($height * $height);
        }

        $patient = Patient::create([
            'user_id' => (int) $data['user_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'cellphone' => $data['cellphone'] ?? null,
            'age' => (int) $data['age'],
            'weight' => $weight,
            'height' => $height,
            'bmi' => $bmi,
            'medical_background' => $data['medical_background'] ?? null,
            'biological_sex' => $data['biological_sex'],
        ]);

        return response()->json([
            'message' => 'Paciente creado correctamente',
            'data' => $patient,
        ], 201);
    }
}
