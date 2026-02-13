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
            $table->foreignId('procedure_id')
                ->constrained('procedures')
                ->after('id');

            $table->unique('procedure_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_evaluations', function (Blueprint $table) {
            $table->dropUnique(['procedure_id']);
            $table->dropConstrainedForeignId('procedure_id');
        });
    }
};
