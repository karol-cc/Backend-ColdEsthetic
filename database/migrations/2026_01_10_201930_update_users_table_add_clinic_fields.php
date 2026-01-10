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
        Schema::table('users', function (Blueprint $table) {
            // eliminar columna vieja
            $table->dropColumn('role');

            // agregar columnas nuevas
            $table->string('brand_name')->nullable()->after('cellphone');
            $table->string('brand_slug')->nullable()->after('brand_name');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->enum('role', ['ADMIN', 'STAFF'])->default('ADMIN');

            $table->dropColumn([
                'brand_name',
                'brand_slug',
            ]);
        });
    }
};