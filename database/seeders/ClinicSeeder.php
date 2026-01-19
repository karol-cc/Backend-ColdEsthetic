<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Patient;
use App\Models\Procedure;
use App\Models\ProcedureItem;

class ClinicSeeder extends Seeder
{
    public function run()
    {
        // Usuario admin
        $user = User::factory()->create([
            'email' => 'admin@clinic.com'
        ]);

        // Pacientes
        $patients = Patient::factory()
            ->count(30)
            ->create(['user_id' => $user->id]);

        foreach ($patients as $patient) {
            // Cada paciente tiene 1–4 procedimientos
            $procedures = Procedure::factory()
                ->count(rand(1, 4))
                ->create([
                    'user_id' => $user->id,
                    'patient_id' => $patient->id,
                ]);

            foreach ($procedures as $procedure) {
                $items = ProcedureItem::factory()
                    ->count(rand(1, 3))
                    ->create([
                        'procedure_id' => $procedure->id,
                    ]);

                // recalcular total_amount
                $procedure->update([
                    'total_amount' => $items->sum('price')
                ]);
            }
        }
    }
}
