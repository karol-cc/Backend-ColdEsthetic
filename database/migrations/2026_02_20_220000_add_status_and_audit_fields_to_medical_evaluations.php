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
            // Campo status con ENUM y default EN_ESPERA
            $table->enum('status', ['EN_ESPERA', 'CONFIRMADO', 'CANCELADO'])
                ->default('EN_ESPERA')
                ->after('bmi_status');

            // Campos de auditoría para confirmación
            $table->timestamp('confirmed_at')->nullable()->after('status');
            $table->foreignId('confirmed_by_user_id')
                ->nullable()
                ->after('confirmed_at')
                ->constrained('users')
                ->nullOnDelete();

            // Campos de auditoría para cancelación
            $table->timestamp('canceled_at')->nullable()->after('confirmed_by_user_id');
            $table->foreignId('canceled_by_user_id')
                ->nullable()
                ->after('canceled_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_evaluations', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by_user_id']);
            $table->dropForeign(['canceled_by_user_id']);
            $table->dropColumn([
                'status',
                'confirmed_at',
                'confirmed_by_user_id',
                'canceled_at',
                'canceled_by_user_id'
            ]);
        });
    }
};
