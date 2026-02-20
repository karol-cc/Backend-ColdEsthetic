<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProcedureRequest;
use App\Http\Requests\UpdateProcedureRequest;
use App\Models\Procedure;
use App\Models\MedicalEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcedureController extends Controller
{
    // LISTAR PROCEDIMIENTOS
    public function index(Request $request)
    {
        $query = Procedure::with([
            'items',
            'medicalEvaluation.patient',
        ]);

        if ($request->filled('medical_evaluation_id')) {
            $query->where(
                'medical_evaluation_id',
                (int) $request->query('medical_evaluation_id')
            );
        }

        return response()->json(
            $query->orderByDesc('procedure_date')->get()
        );
    }

    // VER PROCEDIMIENTO
    public function show(Procedure $procedure)
    {
        $procedure->load([
            'items',
            'medicalEvaluation.patient',
        ]);

        return response()->json($procedure);
    }

    // CREAR PROCEDIMIENTO
    public function store(StoreProcedureRequest $request)
    {
        try {
            $data = $request->validated();

            $medicalEvaluation = MedicalEvaluation::findOrFail(
                (int) $data['medical_evaluation_id']
            );

            $brandSlug = config('app.brand_slug');

            $items = $data['items'];
            $totalAmount = 0.0;

            foreach ($items as $item) {
                $totalAmount += (float) $item['price'];
            }

            $procedure = DB::transaction(function () use ($data, $items, $totalAmount, $brandSlug) {
                $procedure = Procedure::create([
                    'medical_evaluation_id' => (int) $data['medical_evaluation_id'],
                    'brand_slug' => $brandSlug,
                    'procedure_date' => $data['procedure_date'],
                    'notes' => $data['notes'],
                    'total_amount' => $totalAmount,
                ]);

                foreach ($items as $item) {
                    $procedure->items()->create([
                        'item_name' => $item['item_name'],
                        'price' => (float) $item['price'],
                    ]);
                }

                return $procedure;
            });

            $procedure->load([
                'items',
                'medicalEvaluation.patient',
            ]);

            return response()->json([
                'message' => 'Procedimiento creado correctamente',
                'data' => $procedure,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // ACTUALIZAR PROCEDIMIENTO
    public function update(UpdateProcedureRequest $request, Procedure $procedure)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $procedure) {

            if (array_key_exists('procedure_date', $data)) {
                $procedure->procedure_date = $data['procedure_date'];
            }

            if (array_key_exists('notes', $data)) {
                $procedure->notes = $data['notes'];
            }

            if (array_key_exists('items', $data)) {
                $procedure->items()->delete();

                $totalAmount = 0.0;
                foreach ($data['items'] as $item) {
                    $totalAmount += (float) $item['price'];
                    $procedure->items()->create([
                        'item_name' => $item['item_name'],
                        'price' => (float) $item['price'],
                    ]);
                }

                $procedure->total_amount = $totalAmount;
            }

            $procedure->save();
        });

        $procedure->load([
            'items',
            'medicalEvaluation.patient',
        ]);

        return response()->json([
            'message' => 'Procedimiento actualizado correctamente',
            'data' => $procedure,
        ]);
    }
}
