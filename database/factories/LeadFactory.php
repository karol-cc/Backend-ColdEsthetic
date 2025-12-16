<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'service_interest' => $this->faker->randomElement([
                'Lipólisis',
                'Criolipólisis',
                'Rejuvenecimiento Facial',
                'Moldeo Corporal'
            ]),
            'message' => $this->faker->sentence(),
        ];
    }
}
