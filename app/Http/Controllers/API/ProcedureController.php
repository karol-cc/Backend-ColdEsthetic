<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProcedureRequest;
use App\Http\Requests\UpdateProcedureRequest;
use App\Models\Procedure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcedureController extends Controller
{
    public function index(Request $request)
    {
        $query = Procedure::query()->with(['items', 'patient', 'medicalEvaluation']);

        if ($request->filled('patient_id')) {
            $query->where('patient_id', (int) $request->query('patient_id'));
        }

        return response()->json($query->orderByDesc('procedure_date')->get());
    }

    public function show(Procedure $procedure)
    {
        $procedure->load(['items', 'patient', 'medicalEvaluation']);
        return response()->json($procedure);
    }

    public function store(StoreProcedureRequest $request)
    {
        $data = $request->validated();

        $brandSlug = config('app.brand_slug');

        $items = $data['items'];
        $totalAmount = 0.0;
        foreach ($items as $item) {
            $totalAmount += (float) $item['price'];
        }

        $procedure = DB::transaction(function () use ($data, $brandSlug, $items, $totalAmount) {
            $procedure = Procedure::create([
                'user_id' => auth()->id(),
                'patient_id' => (int) $data['patient_id'],
                'brand_slug' => $brandSlug,
                'procedure_date' => $data['procedure_date'],
                'notes' => $data['notes'] ?? null,
                'total_amount' => $totalAmount,
            ]);

            foreach ($items as $item) {
                $procedure->items()->create([
                    'item_name' => $item['item_name'],
                    'price' => (float) $item['price'],
                    'meta' => $item['meta'] ?? null,
                ]);
            }

            return $procedure;
        });

        $procedure->load(['items', 'patient']);

        return response()->json([
            'message' => 'Procedimiento creado correctamente',
            'data' => $procedure,
        ], 201);
    }


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
                // Replace items
                $procedure->items()->delete();

                $totalAmount = 0.0;
                foreach ($data['items'] as $item) {
                    $totalAmount += (float) $item['price'];
                    $procedure->items()->create([
                        'item_name' => $item['item_name'],
                        'price' => (float) $item['price'],
                        'meta' => $item['meta'] ?? null,
                    ]);
                }

                $procedure->total_amount = $totalAmount;
            }

            $procedure->save();
        });

        $procedure->load(['items', 'patient', 'medicalEvaluation']);

        return response()->json([
            'message' => 'Procedimiento actualizado correctamente',
            'data' => $procedure,
        ]);
    }
}
