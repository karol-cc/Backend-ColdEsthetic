<?php

namespace Database\Factories;

use App\Models\Procedure;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProcedureFactory extends Factory
{
    protected $model = Procedure::class;

    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id,
            'patient_id' => Patient::inRandomOrder()->first()?->id,
            'brand_slug' => 'cold-esthetic',
            'procedure_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'total_amount' => 0,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
