<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalEvaluationRequest;
use App\Http\Requests\UpdateMedicalEvaluationRequest;
use App\Models\MedicalEvaluation;
use Illuminate\Support\Facades\DB;

class MedicalEvaluationController extends Controller
{
    // CREAR VALORACIÓN MÉDICA
    public function store(StoreMedicalEvaluationRequest $request)
    {
        $data = $request->validated();

        $weight = $data['weight'];
        $height = $data['height'];

        // Calcular BMI (2 decimales)
        $bmi = round($weight / ($height * $height), 2);

        // Calcular estado BMI
        $bmiStatus = $this->getBmiStatus($bmi);

        $medicalEvaluation = DB::transaction(function () use ($data, $bmi, $bmiStatus) {
            return MedicalEvaluation::create([
                'user_id' => auth()->id(),
                'patient_id' => $data['patient_id'],
                'medical_background' => $data['medical_background'] ?? null,
                'weight' => $data['weight'],
                'height' => $data['height'],
                'bmi' => $bmi,
                'bmi_status' => $bmiStatus,
            ]);
        });

        return response()->json([
            'message' => 'Valoración médica creada correctamente',
            'data' => $medicalEvaluation->load(['patient', 'user']),
        ], 201);
    }

    // ACTUALIZAR VALORACIÓN
    public function update(UpdateMedicalEvaluationRequest $request, MedicalEvaluation $medicalEvaluation)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $medicalEvaluation) {

            if (isset($data['weight'])) {
                $medicalEvaluation->weight = $data['weight'];
            }

            if (isset($data['height'])) {
                $medicalEvaluation->height = $data['height'];
            }

            if (isset($data['medical_background'])) {
                $medicalEvaluation->medical_background = $data['medical_background'];
            }

            // Recalcular BMI solo si cambia peso o altura
            if (isset($data['weight']) || isset($data['height'])) {
                $weight = $medicalEvaluation->weight;
                $height = $medicalEvaluation->height;

                $bmi = round($weight / ($height * $height), 2);

                $medicalEvaluation->bmi = $bmi;
                $medicalEvaluation->bmi_status = $this->getBmiStatus($bmi);
            }

            $medicalEvaluation->save();
        });

        return response()->json([
            'message' => 'Valoración médica actualizada correctamente',
            'data' => $medicalEvaluation->load(['patient', 'user', 'procedures']),
        ]);
    }

    // MOSTRAR ÚLTIMA VALORACIÓN POR PACIENTE
    public function showByPatient(int $patientId)
    {
        $evaluation = MedicalEvaluation::with([
                'patient',
                'procedures.items',
                'user',
            ])
            ->where('patient_id', $patientId)
            ->latest()
            ->first();

        if (!$evaluation) {
            return response()->json([
                'message' => 'Este paciente no tiene valoraciones médicas',
            ], 404);
        }

        return response()->json([
            'data' => $evaluation,
        ]);
    }

    // FUNCIÓN PRIVADA BMI
    private function getBmiStatus(float $bmi): string
    {
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
