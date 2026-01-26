<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClinicalImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClinicalImageController extends Controller
{
    // =========================
    // Controlador para gestionar imágenes clínicas (CRUD)
    // =========================

    // GET - Obtener todos los registros de imágenes clínicas
    // Retorna un listado de imágenes clínicas con sus campos principales
    public function index()
    {
        try {
            // Selecciona los campos principales de la tabla ClinicalImage
            $data = ClinicalImage::select(
                'id',
                'title',
                'before_image',
                'after_image',
                'description',
                'created_at'
            )->get();
            // Devuelve los datos en formato JSON con código 200 (OK)
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            // Si ocurre un error, devuelve el mensaje de error y código 500
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // POST - Crear un nuevo registro de imagen clínica
    // Recibe datos y archivos (imágenes) por request, los valida y guarda
    public function store(Request $request)
    {
        try {
            // Valida los datos recibidos en el request
            $request->validate([
                'title' => 'required|string|max:255', // Título obligatorio
                'description' => 'nullable|string',   // Descripción opcional
                'before_image' => 'required|image|mimes:jpg,jpeg,png,webp', // Imagen "antes" obligatoria
                'after_image' => 'required|image|mimes:jpg,jpeg,png,webp',  // Imagen "después" obligatoria
            ]);

            // Guarda la imagen "antes" en el disco público y obtiene la ruta
            $beforePath = $request->file('before_image')
                ->store('clinical-images', 'public');

            // Guarda la imagen "después" en el disco público y obtiene la ruta
            $afterPath = $request->file('after_image')
                ->store('clinical-images', 'public');

            // Crea el registro en la base de datos con los datos y rutas de imágenes
            $clinicalImage = ClinicalImage::create([
                'title' => $request->title,
                'description' => $request->description,
                'before_image' => $beforePath,
                'after_image' => $afterPath,
                'user_id' => auth()->id(), // Asocia el usuario autenticado
            ]);

            // Devuelve el registro creado en formato JSON
            return response()->json($clinicalImage, 200);
        } catch (\Throwable $th) {
            // Si ocurre un error, devuelve el mensaje de error y código 500
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // PUT/PATCH - Actualizar un registro existente de imagen clínica
    // Permite modificar campos de texto y/o reemplazar imágenes
    public function update(Request $request, $id)
    {
        try {
            // Busca el registro por ID, lanza excepción si no existe
            $item = ClinicalImage::findOrFail($id);

            // Validación flexible: todos los campos son opcionales
            $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'before_image' => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:4096',
                'after_image' => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:4096',
            ]);

            // Si se envía un nuevo título, lo actualiza
            if ($request->has('title')) {
                $item->title = $request->title;
            }
            // Si se envía una nueva descripción, la actualiza
            if ($request->has('description')) {
                $item->description = $request->description;
            }

            // Si se sube una nueva imagen "antes", elimina la anterior y guarda la nueva
            if ($request->hasFile('before_image')) {
                Storage::disk('public')->delete($item->before_image);
                $item->before_image = $request
                    ->file('before_image')
                    ->store('clinical-images', 'public');
            }

            // Si se sube una nueva imagen "después", elimina la anterior y guarda la nueva
            if ($request->hasFile('after_image')) {
                Storage::disk('public')->delete($item->after_image);
                $item->after_image = $request
                    ->file('after_image')
                    ->store('clinical-images', 'public');
            }

            // Guarda los cambios en la base de datos
            $item->save();
            
            // Devuelve mensaje de éxito y el registro actualizado
            return response()->json([
                'message' => 'Contenido actualizado correctamente!!',
                'data' => $item
            ], 200);
        } catch (\Throwable $th) {
            // Si ocurre un error, devuelve el mensaje de error y código 500
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // DELETE - Eliminar un registro de imagen clínica
    // Borra el registro y elimina los archivos de imágenes asociados
    public function destroy($id)
    {
        try {
            // Busca el registro por ID, lanza excepción si no existe
            $item = ClinicalImage::findOrFail($id);

            // Elimina los archivos de imagen "antes" y "después" del disco público
            Storage::disk('public')->delete([
                $item->before_image,
                $item->after_image,
            ]);

            // Elimina el registro de la base de datos
            $item->delete();

            // Devuelve mensaje de éxito
            return response()->json([
                'message' => 'Contenido eliminado'
            ], 200);
        } catch (\Throwable $th) {
            // Si ocurre un error, devuelve el mensaje de error y código 500
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}