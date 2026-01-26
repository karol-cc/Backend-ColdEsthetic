<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalEvaluationRequest;
use App\Http\Requests\UpdateMedicalEvaluationRequest;
use App\Models\MedicalEvaluation;
use Illuminate\Support\Facades\DB;

class MedicalEvaluationController extends Controller
{
    // =========================
    // Controlador para gestionar valoraciones médicas de pacientes
    // =========================

    // CREAR VALORACIÓN MÉDICA
    // Recibe datos validados, calcula el BMI y su estado, y guarda la valoración
    public function store(StoreMedicalEvaluationRequest $request)
    {
        // Obtiene los datos validados del request
        $data = $request->validated();

        $weight = $data['weight']; // Peso del paciente
        $height = $data['height']; // Altura del paciente

        // Calcular BMI (Índice de Masa Corporal) con 2 decimales
        $bmi = round($weight / ($height * $height), 2);

        // Determina el estado del BMI (bajo, normal, sobrepeso, etc.)
        $bmiStatus = $this->getBmiStatus($bmi);

        // Guarda la valoración médica en la base de datos dentro de una transacción
        $medicalEvaluation = DB::transaction(function () use ($data, $bmi, $bmiStatus) {
            return MedicalEvaluation::create([
                'user_id' => auth()->id(), // Usuario que realiza la valoración
                'patient_id' => $data['patient_id'], // Paciente valorado
                'medical_background' => $data['medical_background'] ?? null, // Antecedentes médicos (opcional)
                'weight' => $data['weight'],
                'height' => $data['height'],
                'bmi' => $bmi,
                'bmi_status' => $bmiStatus,
            ]);
        });

        // Devuelve mensaje de éxito y la valoración creada, incluyendo relaciones
        return response()->json([
            'message' => 'Valoración médica creada correctamente',
            'data' => $medicalEvaluation->load(['patient', 'user']),
        ], 201);
    }

    // ACTUALIZAR VALORACIÓN MÉDICA
    // Permite modificar peso, altura y antecedentes médicos, recalculando BMI si corresponde
    public function update(UpdateMedicalEvaluationRequest $request, MedicalEvaluation $medicalEvaluation)
    {
        // Obtiene los datos validados del request
        $data = $request->validated();

        // Actualiza los campos dentro de una transacción
        DB::transaction(function () use ($data, $medicalEvaluation) {

            // Si se envía nuevo peso, lo actualiza
            if (isset($data['weight'])) {
                $medicalEvaluation->weight = $data['weight'];
            }

            // Si se envía nueva altura, la actualiza
            if (isset($data['height'])) {
                $medicalEvaluation->height = $data['height'];
            }

            // Si se envían nuevos antecedentes médicos, los actualiza
            if (isset($data['medical_background'])) {
                $medicalEvaluation->medical_background = $data['medical_background'];
            }

            // Si cambia peso o altura, recalcula el BMI y su estado
            if (isset($data['weight']) || isset($data['height'])) {
                $weight = $medicalEvaluation->weight;
                $height = $medicalEvaluation->height;

                $bmi = round($weight / ($height * $height), 2);

                $medicalEvaluation->bmi = $bmi;
                $medicalEvaluation->bmi_status = $this->getBmiStatus($bmi);
            }

            // Guarda los cambios en la base de datos
            $medicalEvaluation->save();
        });

        // Devuelve mensaje de éxito y la valoración actualizada, incluyendo relaciones
        return response()->json([
            'message' => 'Valoración médica actualizada correctamente',
            'data' => $medicalEvaluation->load(['patient', 'user', 'procedures']),
        ]);
    }

    // MOSTRAR ÚLTIMA VALORACIÓN POR PACIENTE
    // Busca y retorna la última valoración médica de un paciente específico
    public function showByPatient(int $patientId)
    {
        // Busca la última valoración médica del paciente, incluyendo relaciones
        $evaluation = MedicalEvaluation::with([
                'patient',
                'procedures.items',
                'user',
            ])
            ->where('patient_id', $patientId)
            ->latest()
            ->first();

        // Si no existe valoración, retorna mensaje 404
        if (!$evaluation) {
            return response()->json([
                'message' => 'Este paciente no tiene valoraciones médicas',
            ], 404);
        }

        // Devuelve la valoración encontrada
        return response()->json([
            'data' => $evaluation,
        ]);
    }

    // FUNCIÓN PRIVADA: Determina el estado del BMI según el valor calculado
    // Retorna una cadena descriptiva del estado nutricional
    private function getBmiStatus(float $bmi): string
    {
        // Utiliza match para clasificar el BMI en categorías
        return match (true) {
            $bmi < 16.0 => 'Delgadez severa (< 16.0)',
            $bmi < 17.0 => 'Delgadez moderada (16.0–16.9)',
            $bmi < 18.5 => 'Delgadez leve (17.0–18.4)',
            $bmi < 25.0 => 'Peso normal (18.5–24.9)',
            $bmi < 30.0 => 'Sobrepeso (25.0–29.9)',
            $bmi < 35.0 => 'Obesidad grado I (30.0–34.9)',
            $bmi < 40.0 => 'Obesidad grado II (35.0–39.9)',
            default => 'Obesidad grado III (≥ 40)',
        };
    }
}
