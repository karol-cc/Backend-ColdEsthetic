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


    //Registrarse
    public function register(Request $request)
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
            ]);

            return response()->json([
                'message' => 'Admin creado correctamente!!'
            ], 201);
            
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    //Login
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

            $request->session()->regenerate();

            return response()->json([
                'user' => Auth::user(),
                'message' => 'Inicio de sesión exitoso'
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    //Logout
    public function logout(Request $request)
    {
        Auth::guard('web')->logout(); // invalida la sesión

        // Opcional: invalidar también la cookie de Sanctum
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out'], 200);
    }

    
}
