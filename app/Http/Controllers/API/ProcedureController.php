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
    // =========================
    // Controlador para gestionar procedimientos médicos (listar, ver, crear, actualizar)
    // =========================

    // LISTAR PROCEDIMIENTOS
    // Permite filtrar por valoración médica y devuelve procedimientos con sus items y paciente
    public function index(Request $request)
    {
        // Consulta procedimientos con relaciones: items y valoración médica (con paciente)
        $query = Procedure::with([
            'items',
            'medicalEvaluation.patient',
        ]);

        // Si se recibe parámetro 'medical_evaluation_id', filtra por ese ID
        if ($request->filled('medical_evaluation_id')) {
            $query->where(
                'medical_evaluation_id',
                (int) $request->query('medical_evaluation_id')
            );
        }

        // Devuelve la lista ordenada por fecha de procedimiento descendente
        return response()->json(
            $query->orderByDesc('procedure_date')->get()
        );
    }

    // VER PROCEDIMIENTO
    // Muestra los datos de un procedimiento específico, incluyendo items y paciente
    public function show(Procedure $procedure)
    {
        // Carga relaciones: items y valoración médica (con paciente)
        $procedure->load([
            'items',
            'medicalEvaluation.patient',
        ]);

        // Devuelve el procedimiento con las relaciones cargadas
        return response()->json($procedure);
    }

    // CREAR PROCEDIMIENTO
    // Recibe datos validados, calcula el total, crea el procedimiento y sus items
    public function store(StoreProcedureRequest $request)
    {
        $data = $request->validated(); // Obtiene datos validados

        // Busca la valoración médica asociada, lanza excepción si no existe
        $medicalEvaluation = MedicalEvaluation::findOrFail(
            (int) $data['medical_evaluation_id']
        );

        $brandSlug = config('app.brand_slug'); // Obtiene el slug de la marca desde la config

        $items = $data['items']; // Items del procedimiento
        $totalAmount = 0.0; // Inicializa el total

        // Suma el precio de todos los items
        foreach ($items as $item) {
            $totalAmount += (float) $item['price'];
        }

        // Crea el procedimiento y sus items dentro de una transacción
        $procedure = DB::transaction(function () use ($data, $items, $totalAmount, $brandSlug) {
            $procedure = Procedure::create([
                'medical_evaluation_id' => (int) $data['medical_evaluation_id'],
                'brand_slug' => $brandSlug,
                'procedure_date' => $data['procedure_date'],
                'notes' => $data['notes'] ?? null,
                'total_amount' => $totalAmount,
            ]);

            // Crea cada item asociado al procedimiento
            foreach ($items as $item) {
                $procedure->items()->create([
                    'item_name' => $item['item_name'],
                    'price' => (float) $item['price'],
                    'meta' => $item['meta'] ?? null,
                ]);
            }

            return $procedure;
        });

        // Carga relaciones para la respuesta
        $procedure->load([
            'items',
            'medicalEvaluation.patient',
        ]);

        // Devuelve mensaje de éxito y el procedimiento creado
        return response()->json([
            'message' => 'Procedimiento creado correctamente',
            'data' => $procedure,
        ], 201);
    }

    // ACTUALIZAR PROCEDIMIENTO
    // Permite modificar fecha, notas y/o items del procedimiento, recalculando el total
    public function update(UpdateProcedureRequest $request, Procedure $procedure)
    {
        $data = $request->validated(); // Obtiene datos validados

        // Actualiza los campos dentro de una transacción
        DB::transaction(function () use ($data, $procedure) {

            // Si se envía nueva fecha, la actualiza
            if (array_key_exists('procedure_date', $data)) {
                $procedure->procedure_date = $data['procedure_date'];
            }

            // Si se envían nuevas notas, las actualiza
            if (array_key_exists('notes', $data)) {
                $procedure->notes = $data['notes'];
            }

            // Si se envían nuevos items, elimina los anteriores y crea los nuevos, recalculando el total
            if (array_key_exists('items', $data)) {
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

            // Guarda los cambios en la base de datos
            $procedure->save();
        });

        // Carga relaciones para la respuesta
        $procedure->load([
            'items',
            'medicalEvaluation.patient',
        ]);

        // Devuelve mensaje de éxito y el procedimiento actualizado
        return response()->json([
            'message' => 'Procedimiento actualizado correctamente',
            'data' => $procedure,
        ]);
    }
}
