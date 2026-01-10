<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    //Registrarse 
    public function register(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'cellphone'  => 'required|string|max:20',
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
    }

    //Login
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'message' => 'Inicio de sesión exitosa!!'
        ]);
    }
}
