<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalEvaluationRequest;
use App\Http\Requests\UpdateMedicalEvaluationRequest;
use App\Models\MedicalEvaluation;
use App\Models\Procedure;
use Illuminate\Support\Facades\DB;

class MedicalEvaluationController extends Controller
{
    //Crear
    public function store(StoreMedicalEvaluationRequest $request)
    {
        $data = $request->validated();

        $procedure = Procedure::findOrFail((int) $data['procedure_id']);
        if ((int) $procedure->patient_id !== (int) $data['patient_id']) {
            return response()->json([
                'message' => 'El patient_id no coincide con el procedimiento',
            ], 422);
        }

        $evaluation = DB::transaction(function () use ($data) {
            return MedicalEvaluation::create([
                'procedure_id' => (int) $data['procedure_id'],
                'user_id' => auth()->id(),
                'patient_id' => (int) $data['patient_id'],
                'evaluation_data' => $data['evaluation_data'],
                'notes' => $data['notes'] ?? null,
            ]);
        });

        $evaluation->load(['procedure', 'patient', 'user']);

        return response()->json([
            'message' => 'Valoración creada correctamente',
            'data' => $evaluation,
        ], 201);
    }

    //Actualizar
    public function update(UpdateMedicalEvaluationRequest $request, MedicalEvaluation $medicalEvaluation)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $medicalEvaluation) {
            if (array_key_exists('evaluation_data', $data)) {
                $medicalEvaluation->evaluation_data = $data['evaluation_data'];
            }

            if (array_key_exists('notes', $data)) {
                $medicalEvaluation->notes = $data['notes'];
            }

            $medicalEvaluation->save();
        });

        $medicalEvaluation->load(['procedure', 'patient', 'user']);

        return response()->json([
            'message' => 'Valoración actualizada correctamente',
            'data' => $medicalEvaluation,
        ]);
    }
}
