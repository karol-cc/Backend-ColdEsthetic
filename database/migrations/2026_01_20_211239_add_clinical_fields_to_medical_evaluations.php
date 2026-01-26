<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('medical_evaluations', function (Blueprint $table) {
            $table->text('medical_background')->nullable()->after('patient_id');

            $table->float('weight')->nullable()->after('medical_background');
            $table->float('height')->nullable()->after('weight');
            $table->decimal('bmi', 6, 2)->nullable()->after('height');
            $table->string('bmi_status')->nullable()->after('bmi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_evaluations', function (Blueprint $table) {
            $table->text('medical_background')->nullable()->after('patient_id');

            $table->float('weight')->nullable()->after('medical_background');
            $table->float('height')->nullable()->after('weight');
            $table->decimal('bmi', 6, 2)->nullable()->after('medical_background');
            $table->string('bmi_status')->nullable()->after('bmi');
        });
    }
};
