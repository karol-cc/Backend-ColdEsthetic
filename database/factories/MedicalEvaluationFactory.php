<?php

namespace Database\Factories;

use App\Models\MedicalEvaluation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicalEvaluationFactory extends Factory
{
    protected $model = MedicalEvaluation::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'patient_id' => Patient::factory(),

            'medical_background' => $this->faker->sentence(),

            // solo datos crudos
            'weight' => $this->faker->numberBetween(50, 90),
            'height' => $this->faker->numberBetween(150, 180),

            // estos los setea el controller
            'bmi' => null,
            'bmi_status' => null,
        ];
    }
}
