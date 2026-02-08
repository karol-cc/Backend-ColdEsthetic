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
            $table->string('referrer_name', 50)->change();
            $table->string('first_name', 100)->change();
            $table->string('last_name', 100)->change();
            $table->string('cellphone', 15)->nullable(false)->change();
            $table->integer('age')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('referrer_name', 255)->change();
            $table->string('first_name', 255)->change();
            $table->string('last_name', 255)->change();
            $table->string('cellphone', 255)->nullable()->change();
            $table->integer('age')->nullable()->change();
        });
    }
};
