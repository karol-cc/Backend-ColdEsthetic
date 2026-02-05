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
        Schema::table('procedures', function (Blueprint $table) {
            $table->unsignedBigInteger('medical_evaluation_id')
                ->after('id');

            $table->foreign('medical_evaluation_id')
                ->references('id')
                ->on('medical_evaluations')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            
            $table->dropForeign(['medical_evaluation_id']);
            $table->dropColumn('medical_evaluation_id');
        });
    }
};
