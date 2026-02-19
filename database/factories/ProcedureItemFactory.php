<?php

namespace Database\Factories;

use App\Models\ProcedureItem;
use App\Models\Procedure;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProcedureItemFactory extends Factory
{
    protected $model = ProcedureItem::class;

    public function definition()
    {
        return [
            'procedure_id' => Procedure::inRandomOrder()->first()?->id,
            'item_name' => $this->faker->randomElement([
                'Abdomen Completo',
                'Laterales',
                'Cintura',
                'Espalda Completa',
                'Coxis',
                'Brazos',
                'Láser Básico'
            ]),
            'price' => $this->faker->randomFloat(2, 100, 800),
        ];
    }
}
