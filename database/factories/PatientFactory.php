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
        return [
            'user_id' => User::inRandomOrder()->first()?->id,
            'referrer_name' => $this->faker->randomElement([
                'Dr. Adele',
                'Dr. Fernanda',
                'Dr. Alexander'
            ]),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'cellphone' => $this->faker->phoneNumber(),
            'age' => $this->faker->numberBetween(18, 65),
            'weight' => $this->faker->randomFloat(1, 50, 100),
            'height' => $this->faker->randomFloat(2, 1.5, 1.9),
            'bmi' => null,
            'biological_sex' => $this->faker->randomElement(['Female', 'Male', 'Other']),
            'medical_background' => $this->faker->optional()->sentence(),
        ];
    }
}
