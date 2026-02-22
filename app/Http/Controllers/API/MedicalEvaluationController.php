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
        try {
            
            $data = $request->validated();

            $user = auth()->user(); // obtenemos el usuario autenticado
            if (!$user) {
                return response()->json([
                    'message' => 'No autenticado'
                ], 401);
            }

            // Bloquear si está inactivo o despedido
            if ($user->status !== User::STATUS_ACTIVE) {
                return response()->json([
                    'message' => 'Tu cuenta no está activa. No puedes registrar pacientes.'
                ], 403);
            }

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
                    'medical_background' => $data['medical_background'],
                    'weight' => $data['weight'],
                    'height' => $data['height'],
                    'bmi' => $bmi,
                    'bmi_status' => $bmiStatus,
                    'status' => MedicalEvaluation::STATUS_EN_ESPERA,
                    
                ]);
            });

            return response()->json([
                'message' => 'Valoración médica creada correctamente',
                'data' => $medicalEvaluation->load(['patient', 'user']),
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // ACTUALIZAR VALORACIÓN
    public function update(UpdateMedicalEvaluationRequest $request, MedicalEvaluation $medicalEvaluation)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'No autenticado'
            ], 401);
        }

        if ($user->status !== User::STATUS_ACTIVE) {
            return response()->json([
                'message' => 'Tu cuenta no está activa. No puedes actualizar valoraciones.'
            ], 403);
        }

        $data = $request->validated();

        // Bloquear edición si está confirmado
        if ($medicalEvaluation->isConfirmado()) {
            return response()->json([
                'message' => 'No se pueden editar datos clínicos de una valoración confirmada. Debe cancelarla primero.'
            ], 403);
        }

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

    // CONFIRMAR VALORACIÓN
    public function confirmar(MedicalEvaluation $medicalEvaluation)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'No autenticado'
            ], 401);
        }

        if ($user->status !== User::STATUS_ACTIVE) {
            return response()->json([
                'message' => 'Tu cuenta no está activa. No puedes confirmar valoraciones.'
            ], 403);
        }

        try {
            if ($medicalEvaluation->isConfirmado()) {
                return response()->json([
                    'message' => 'La valoración ya está confirmada',
                    'data' => $medicalEvaluation->load(['patient', 'user', 'confirmedBy']),
                ]);
            }

            DB::transaction(function () use ($medicalEvaluation) {
                $medicalEvaluation->status = MedicalEvaluation::STATUS_CONFIRMADO;
                $medicalEvaluation->confirmed_at = now();
                $medicalEvaluation->confirmed_by_user_id = auth()->id();
                $medicalEvaluation->canceled_at = null;
                $medicalEvaluation->canceled_by_user_id = null;
                $medicalEvaluation->save();
            });

            return response()->json([
                'message' => 'Valoración confirmada correctamente',
                'data' => $medicalEvaluation->load(['patient', 'user', 'confirmedBy']),
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // CANCELAR VALORACIÓN
    public function cancelar(MedicalEvaluation $medicalEvaluation)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'No autenticado'
            ], 401);
        }

        if ($user->status !== User::STATUS_ACTIVE) {
            return response()->json([
                'message' => 'Tu cuenta no está activa. No puedes cancelar valoraciones.'
            ], 403);
        }

        try {
            if ($medicalEvaluation->isCancelado()) {
                return response()->json([
                    'message' => 'La valoración ya está cancelada',
                    'data' => $medicalEvaluation->load(['patient', 'user', 'canceledBy']),
                ]);
            }

            DB::transaction(function () use ($medicalEvaluation) {
                $medicalEvaluation->status = MedicalEvaluation::STATUS_CANCELADO;
                $medicalEvaluation->canceled_at = now();
                $medicalEvaluation->canceled_by_user_id = auth()->id();
                $medicalEvaluation->confirmed_at = null;
                $medicalEvaluation->confirmed_by_user_id = null;
                $medicalEvaluation->save();
            });

            return response()->json([
                'message' => 'Valoración cancelada correctamente',
                'data' => $medicalEvaluation->load(['patient', 'user', 'canceledBy']),
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // MOSTRAR TODAS LAS VALORACIONES POR PACIENTE
    public function showByPatient(int $patientId)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'No autenticado'
            ], 401);
        }

        if ($user->status !== User::STATUS_ACTIVE) {
            return response()->json([
                'message' => 'Tu cuenta no está activa.'
            ], 403);
        }

        $evaluation = MedicalEvaluation::with([
                'patient',
                'procedures.items',
                'user',
                'confirmedBy',
                'canceledBy',
            ])
            ->where('patient_id', $patientId)
            ->latest()
            ->get();

        
        if ($evaluation->isEmpty()) {
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
