<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{

    // Editar campos del admin
    public function updateAdmin(Request $request, $id)
    {
        try {

            $user = User::findOrFail($id);

            // Verificar que sea admin
            if ($user->role !== User::ROLE_ADMIN) {
                return response()->json([
                    'message' => 'Solo se puede modificar un administrador'
                ], 400);
            }
            if (auth()->id() !== $user->id) {
                return response()->json([
                    'message' => 'Solo puedes modificar tu propia cuenta.'
                ], 403);
            }

            // Validación
            $request->validate([
                'name'     => 'sometimes|string|max:50',
                'first_name' => 'sometimes|string|max:100',
                'last_name'  => 'sometimes|string|max:100',
                'email'    => 'sometimes|email|unique:users,email,' . $id,
                'password' => 'sometimes|min:6'
            ]);

            // Evitar que cambie rol o status
            $user->update($request->only([
                'name',
                'email',
                'first_name',
                'last_name',
                'password'
            ]));

            return response()->json([
                'message' => 'Administrador actualizado correctamente',
                'user' => $user
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // Listar remitentes
    public function listRemitentes()
    {
        try {
            $remitentes = User::where('role', User::ROLE_REMITENTE)->get();
            return response()->json($remitentes);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // Crear remitente
    public function createRemitente(Request $request)
    {
        try {
            $request->validate([
                'name'       => 'required|string|max:50',
                'first_name' => 'required|string|max:100',
                'last_name'  => 'required|string|max:100',
                'cellphone'  => 'required|string|max:15',
                'email'      => 'required|email|unique:users',
                'password'   => 'required|min:6'
            ]);

            $user = User::create([
                'name'       => $request->name,
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'cellphone'  => $request->cellphone,
                'brand_name' => config('app.brand_name'),
                'brand_slug' => config('app.brand_slug'),
                'email'      => $request->email,
                'password'   => $request->password,
                'role'       => User::ROLE_REMITENTE,
                'status' => User::STATUS_ACTIVE,
            ]);

            return response()->json([
                'message' => 'Remitente creado correctamente',
                'user' => $user
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // Modificar remitente
    public function updateRemitente(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Verificar que sea remitente
            if ($user->role !== User::ROLE_REMITENTE) {
                return response()->json([
                    'message' => 'Solo se pueden modificar remitentes'
                ], 400);
            }

            if ($user->status === User::STATUS_FIRED) {
                return response()->json([
                    'message' => 'No se puede modificar un remitente despedido. Debe activarse primero.'
                ], 400);
            }

            // Validación
            $request->validate([
                'name'       => 'sometimes|string|max:50',
                'first_name' => 'sometimes|string|max:100',
                'last_name'  => 'sometimes|string|max:100',
                'cellphone'  => 'sometimes|string|max:15',
                'email'      => 'sometimes|email|unique:users,email,' . $id,
                'password'   => 'sometimes|min:6'
            ]);

            // Actualizar datos permitidos
            $user->update($request->only([
                'name',
                'first_name',
                'last_name',
                'cellphone',
                'email',
                'password'
            ]));

            return response()->json([
                'message' => 'Remitente actualizado correctamente',
                'user' => $user
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // Activar remitente
    public function activarRemitente($id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->role !== User::ROLE_REMITENTE) {
                return response()->json([
                    'message' => 'Solo se pueden modificar remitentes'
                ], 400);
            }

            $user->status = User::STATUS_ACTIVE;
            $user->save();

            return response()->json([
                'message' => 'Remitente activado correctamente',
                'user' => $user
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // Inactivar remitente (pausa temporal)
    public function inactivarRemitente($id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->role !== User::ROLE_REMITENTE) {
                return response()->json([
                    'message' => 'Solo se pueden modificar remitentes'
                ], 400);
            }

            $user->status = User::STATUS_INACTIVE;
            $user->save();

            return response()->json([
                'message' => 'Remitente inactivado correctamente',
                'user' => $user
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // Despedir remitente
    public function despedirRemitente($id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->role !== User::ROLE_REMITENTE) {
                return response()->json([
                    'message' => 'Solo se pueden modificar remitentes'
                ], 400);
            }

            $user->status = User::STATUS_FIRED;
            $user->save();

            return response()->json([
                'message' => 'Remitente marcado como despedido',
                'user' => $user
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
