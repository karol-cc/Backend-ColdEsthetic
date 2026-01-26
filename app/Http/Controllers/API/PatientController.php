<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientRequest;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    // =========================
    // Controlador para gestionar pacientes (listar, ver, crear)
    // =========================

    // LISTAR PACIENTES
    // Permite buscar pacientes por nombre, apellido o celular (opcional)
    public function index(Request $request)
    {
        $query = Patient::query(); // Inicia consulta base

        // Si se recibe parámetro 'search', filtra por nombre, apellido o celular
        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('cellphone', 'like', "%{$search}%");
            });
        }

        // Devuelve la lista de pacientes ordenada por ID descendente
        return response()->json(
            $query->orderByDesc('id')->get()
        );
    }

    // VER PACIENTE
    // Muestra los datos de un paciente específico, incluyendo valoraciones, procedimientos y usuario asociado
    public function show(Patient $patient)
    {
        // Carga relaciones: valoraciones médicas (con procedimientos e items) y usuario
        $patient->load([
            'medicalEvaluations.procedures.items',
            'user',
        ]);

        // Devuelve el paciente con las relaciones cargadas
        return response()->json($patient);
    }

    // CREAR PACIENTE
    // Recibe datos validados y crea un nuevo paciente asociado al usuario autenticado
    public function store(StorePatientRequest $request)
    {
        $data = $request->validated(); // Obtiene datos validados

        $userId = auth()->id(); // Obtiene el ID del usuario autenticado
        if (!$userId) {
            // Si no hay usuario autenticado, retorna error 401
            return response()->json([
                'message' => 'No autenticado'
            ], 401);
        }

        // Crea el paciente con los datos recibidos y el usuario asociado
        $patient = Patient::create([
            'user_id' => $userId,
            'referrer_name' => $data['referrer_name'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'cellphone' => $data['cellphone'] ?? null,
            'age' => (int) $data['age'],
            'biological_sex' => $data['biological_sex'],
        ]);

        // Devuelve mensaje de éxito y el paciente creado
        return response()->json([
            'message' => 'Paciente creado correctamente',
            'data' => $patient,
        ], 201);
    }
}
