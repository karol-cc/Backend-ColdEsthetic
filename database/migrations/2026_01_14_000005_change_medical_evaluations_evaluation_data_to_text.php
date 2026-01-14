<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement('ALTER TABLE medical_evaluations MODIFY evaluation_data LONGTEXT');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE medical_evaluations ALTER COLUMN evaluation_data TYPE TEXT');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement('ALTER TABLE medical_evaluations MODIFY evaluation_data JSON');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE medical_evaluations ALTER COLUMN evaluation_data TYPE JSON');
        }
    }
};
