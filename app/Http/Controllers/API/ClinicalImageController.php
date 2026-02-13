<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClinicalImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClinicalImageController extends Controller
{
    // GET - obtener todos
    public function index()
    {
        try {
            $data = ClinicalImage::select(
                'id',
                'title',
                'before_image',
                'after_image',
                'description',
                'created_at'
            )->get();
            
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // POST - crear contenido con imágenes
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:100',
                'description' => 'nullable|string',
                'before_image' => 'required|image|mimes:jpg,jpeg,png,webp',
                'after_image' => 'required|image|mimes:jpg,jpeg,png,webp',
            ]);

            $beforePath = $request->file('before_image')
                ->store('clinical-images', 'public');

            $afterPath = $request->file('after_image')
                ->store('clinical-images', 'public');

            $clinicalImage = ClinicalImage::create([
                'title' => $request->title,
                'description' => $request->description,
                'before_image' => $beforePath,
                'after_image' => $afterPath,
                'user_id' => auth()->id(),
            ]);

            return response()->json($clinicalImage, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // UPDATE - modificar contenido
    public function update(Request $request, $id)
    {
        try {
            $item = ClinicalImage::findOrFail($id);

            // 1. Validación flexible (nada obligatorio)
            $request->validate([
                'title' => 'sometimes|string|max:100',
                'description' => 'sometimes|string',
                'before_image' => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:4096',
                'after_image' => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:4096',
            ]);

            // 2. Campos de texto
            if ($request->has('title')) {
                $item->title = $request->title;
            }
            if ($request->has('description')) {
                $item->description = $request->description;
            }

            // 3. Reemplazar imagen BEFORE
            if ($request->hasFile('before_image')) {
                Storage::disk('public')->delete($item->before_image);
                $item->before_image = $request
                    ->file('before_image')
                    ->store('clinical-images', 'public');
            }

            // 4. Reemplazar imagen AFTER
            if ($request->hasFile('after_image')) {
                Storage::disk('public')->delete($item->after_image);
                $item->after_image = $request
                    ->file('after_image')
                    ->store('clinical-images', 'public');
            }

            // 5. Guardar cambios
            $item->save();
            
            return response()->json([
                'message' => 'Contenido actualizado correctamente!!',
                'data' => $item
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // DELETE
    public function destroy($id)
    {
        try {
            $item = ClinicalImage::findOrFail($id);

            Storage::disk('public')->delete([
                $item->before_image,
                $item->after_image,
            ]);

            $item->delete();

            return response()->json([
                'message' => 'Contenido eliminado'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}