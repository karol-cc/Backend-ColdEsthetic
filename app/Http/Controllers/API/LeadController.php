<?php

namespace App\Http\Controllers\API;

use App\Models\Lead;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;

class LeadController extends Controller
{

    // Obtener todos
    public function index()
    {
        $leads = Lead::orderBy('created_at', 'desc')->get();
        return response()->json($leads, 200);
    }

    // Buscar por ID
    public function show($id)
    {
        try {
            $lead = Lead::findOrFail($id);

            return response()->json($lead, 200);

        } catch (Exception $e) {
            return response()->json([
                'mensaje' => 'No se encontró el registro',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    // Crear formulario
    public function create(Request $request)
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => 'required|email|max:255',
                'service_interest' => 'required|in:Lipólisis,Criolipólisis,Rejuvenecimiento Facial,Moldeo Corporal',
                'message' => 'nullable|string',
            ]);

            Lead::create($validated);

            return response()->json([
                'mensaje' => 'Datos guardados correctamente'
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'mensaje' => 'Ocurrió un error al guardar los datos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //Estadística básica
    public function stats()
    {
        $stats = Lead::select('service_interest')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('service_interest')
            ->get();

        return response()->json($stats, 200);
    }
}
