<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name'  => $this->faker->lastName(),
            'cellphone' => $this->faker->numerify('##########'), // 10 dígitos
            'brand_name' => config('app.brand_name'), 
            'brand_slug' => config('app.brand_slug'), 
            'name' => $this->faker->unique()->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'), // simple para pruebas
            'role' => User::ROLE_REMITENTE,
            'status' => User::STATUS_ACTIVE,
        ];
    }

    public function admin()
    {
        return $this->state([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    public function remitente()
    {
        return $this->state([
            'role' => User::ROLE_REMITENTE,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    public function inactivo()
    {
        return $this->state([
            'status' => User::STATUS_INACTIVE,
        ]);
    }

    public function despedido()
    {
        return $this->state([
            'status' => User::STATUS_FIRED,
        ]);
    }
}

