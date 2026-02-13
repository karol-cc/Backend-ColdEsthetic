<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientRequest;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    // LISTAR PACIENTES
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

        return response()->json(
            $query->orderByDesc('id')->get()
        );
    }

    // VER PACIENTE
    public function show(Patient $patient)
    {
        try {
            $patient->load([
                'medicalEvaluations.procedures.items',
                'user',
            ]);

            return response()->json($patient);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // CREAR PACIENTE
    public function store(StorePatientRequest $request)
    {
        try {
            $data = $request->validated();

            $userId = auth()->id();
            if (!$userId) {
                return response()->json([
                    'message' => 'No autenticado'
                ], 401);
            }

            $patient = Patient::create([
                'user_id' => $userId,
                'referrer_name' => $data['referrer_name'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'cellphone' => $data['cellphone'],
                'age' => (int) $data['age'],
                'biological_sex' => $data['biological_sex'],
            ]);

            return response()->json([
                'message' => 'Paciente creado correctamente',
                'data' => $patient,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
