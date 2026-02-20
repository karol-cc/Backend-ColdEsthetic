<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    // Me (usuario autenticado)
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    // Login
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email'    => 'required|email',
                'password' => 'required'
            ]);

            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'message' => 'Credenciales incorrectas'
                ], 401);
            }

            $user = Auth::user();

        // Verificar si el remitente está activo
        if ($user->status !== User::STATUS_ACTIVE) {
            Auth::logout();
            return response()->json([
                'message' => 'Tu cuenta no está activa.'
            ], 403);
        }

            $request->session()->regenerate();

            return response()->json([
                'user' => Auth::user(),
                'message' => 'Inicio de sesión exitoso'
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // Log out
    public function logout(Request $request)
    {
        Auth::guard('web')->logout(); // invalida la sesión

        // Opcional: invalidar también la cookie de Sanctum
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out'], 200);
    }
}
