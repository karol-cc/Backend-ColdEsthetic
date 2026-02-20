<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition()
    {
        // Seleccionamos un usuario aleatorio 
        $user = User::inRandomOrder()->first();

        return [
            'user_id' => $user?->id,
            'referrer_name' => $user?->name, // ahora se usa el name del remitente
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'cellphone' => $this->faker->numerify('##########'), // 10 dígitos
            'age' => $this->faker->numberBetween(18, 65),
            'biological_sex' => $this->faker->randomElement(['Femenino', 'Masculino', 'Otro']),
            'cedula' => $this->faker->unique()->numerify('##########'),
        ];
    }
}
