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
            $table->text('medical_background')->nullable(false)->change();
            $table->float('weight')->nullable(false)->change();
            $table->float('height')->nullable(false)->change();
            $table->decimal('bmi', 6, 2)->nullable(false)->change();
            $table->string('bmi_status', 50)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_evaluations', function (Blueprint $table) {
            $table->text('medical_background')->nullable()->change();
            $table->float('weight')->nullable()->change();
            $table->float('height')->nullable()->change();
            $table->decimal('bmi', 6, 2)->nullable()->change();
            $table->string('bmi_status', 255)->nullable()->change();
        });
    }
};
