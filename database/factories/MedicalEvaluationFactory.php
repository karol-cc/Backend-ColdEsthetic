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
        $weight = $this->faker->numberBetween(50, 90);
        $height = $this->faker->numberBetween(150, 180);

        // BMI = peso / (altura en metros ^ 2)
        // Como faker da altura en cm, conviértela a metros
        $heightInMeters = $height / 100;
        $bmi = round($weight / ($heightInMeters * $heightInMeters), 2);

        // Estado BMI (puedes reutilizar la misma lógica que en tu controlador)
        $bmiStatus = match (true) {
            $bmi < 16.0 => 'Delgadez severa (< 16.0)',
            $bmi < 17.0 => 'Delgadez moderada (16.0–16.9)',
            $bmi < 18.5 => 'Delgadez leve (17.0–18.4)',
            $bmi < 25.0 => 'Peso normal (18.5–24.9)',
            $bmi < 30.0 => 'Sobrepeso (25.0–29.9)',
            $bmi < 35.0 => 'Obesidad grado I (30.0–34.9)',
            $bmi < 40.0 => 'Obesidad grado II (35.0–39.9)',
            default => 'Obesidad grado III (≥ 40)',
        };

        return [
            'user_id' => User::factory(),
            'patient_id' => Patient::factory(),
            'medical_background' => $this->faker->sentence(),
            'weight' => $weight,
            'height' => $height,
            'bmi' => $bmi,
            'bmi_status' => $bmiStatus,
            'status' => MedicalEvaluation::STATUS_EN_ESPERA,
        ];
    }
}
