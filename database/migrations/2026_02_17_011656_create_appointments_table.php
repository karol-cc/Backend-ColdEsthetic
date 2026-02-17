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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('patient_id')->constrained('patients');
            $table->string('referrer_name');
            $table->dateTime('appointment_datetime');
            $table->integer('duration_minutes')->default(60);
            $table->json('planned_procedures');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->string('google_calendar_event_id')->nullable();
            $table->foreignId('procedure_id')->nullable()->constrained('procedures');
            $table->timestamps();

            // Indexes for better query performance
            $table->index('appointment_datetime');
            $table->index('status');
            $table->index('google_calendar_event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
