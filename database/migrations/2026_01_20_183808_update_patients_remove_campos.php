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
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('weight');
            $table->dropColumn('height');
            $table->dropColumn('bmi');
            $table->dropColumn('medical_background');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->text('weight')->nullable();
            $table->text('height')->nullable();
            $table->text('bmi')->nullable();
            $table->text('medical_background')->nullable();
        });
    }
};
