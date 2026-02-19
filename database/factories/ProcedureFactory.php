<?php

namespace Database\Factories;

use App\Models\Procedure;
use App\Models\MedicalEvaluation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProcedureFactory extends Factory
{
    protected $model = Procedure::class;

    public function definition(): array
    {
        return [
            'medical_evaluation_id' => MedicalEvaluation::factory(),
            'procedure_date' => $this->faker->date(),
            'total_amount' => $this->faker->numberBetween(100, 1000),
            'brand_slug' => config('app.brand_slug'), 
            'notes' => $this->faker->sentence(),
        ];
    }
}