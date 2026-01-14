<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientRequest;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $query = Patient::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('cellphone', 'like', "%{$search}%");
            });
        }

        return response()->json($query->orderByDesc('id')->get());
    }

    public function show(Patient $patient)
    {
        $patient->load(['procedures.items', 'medicalEvaluations', 'user']);
        return response()->json($patient);
    }

    public function store(StorePatientRequest $request)
    {
        $data = $request->validated();

        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $weight = (float) $data['weight'];
        $height = (float) $data['height'];
        $bmi = null;

        if ($height > 0) {
            $bmi = $weight / ($height * $height);
        }

        $patient = Patient::create([
            'user_id' => (int) $userId,
            'referrer_name' => $data['referrer_name'],
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
