<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Patient;
use App\Models\MedicalEvaluation;
use App\Models\Procedure;
use App\Models\ProcedureItem;

class ClinicSeeder extends Seeder
{
    public function run()
    {
        // Crear admin y guardarlo en variable
        $admin = User::factory()
            ->admin()
            ->create([
                'email' => 'admin@clinic.com',
            ]);

        // Crear remitentes activos
        $remitentesActivos = User::factory()
            ->remitente()
            ->count(5)
            ->create();

        // Crear remitentes inactivos
        $remitentesInactivos = User::factory()
            ->remitente()
            ->inactivo()
            ->count(2)
            ->create();

        // Crear remitentes despedidos
        $remitentesDespedidos = User::factory()
            ->remitente()
            ->despedido()
            ->count(2)
            ->create();

        // Juntar todos los remitentes en una colección
        $remitentes = $remitentesActivos
            ->concat($remitentesInactivos)
            ->concat($remitentesDespedidos);

        // Pacientes asociados al admin (o a remitentes aleatorios)
        $patients = Patient::factory()
            ->count(30)
            ->create([
                'user_id' => $admin->id, // admin
            ]);

        foreach ($patients as $patient) {
            // Cada paciente tiene 1–2 evaluaciones médicas
            $medicalEvaluations = MedicalEvaluation::factory()
                ->count(rand(1, 2))
                ->create([
                    'user_id' => $admin->id, // también se puede usar $remitentes->random()->id
                    'patient_id' => $patient->id,
                ]);

            foreach ($medicalEvaluations as $medical) {
                // Cada evaluación tiene 1–4 procedimientos
                $procedures = Procedure::factory()
                    ->count(rand(1, 4))
                    ->create([
                        'medical_evaluation_id' => $medical->id,
                        'brand_slug' => config('app.brand_slug'),
                        'procedure_date' => now()->subDays(rand(0, 60)),
                    ]);

                foreach ($procedures as $procedure) {
                    $items = ProcedureItem::factory()
                        ->count(rand(1, 3))
                        ->create([
                            'procedure_id' => $procedure->id,
                        ]);
                    // Recalcular total del procedimiento
                    $procedure->update([
                        'total_amount' => $items->sum('price'),
                    ]);
                }
            }
        }
    }
}
