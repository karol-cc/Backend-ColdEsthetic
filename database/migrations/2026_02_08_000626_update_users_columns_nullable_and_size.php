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
            $table->string('name', 50)->change();
            $table->string('email', 100)->change();
            $table->string('first_name', 100)->change();
            $table->string('last_name', 100)->change();
            $table->string('cellphone', 15)->change();

            $table->string('brand_name', 50)->nullable(false)->change();
            $table->string('brand_slug', 50)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name', 255)->change();
            $table->string('email', 255)->change();
            $table->string('first_name', 255)->change();
            $table->string('last_name', 255)->change();
            $table->string('cellphone', 255)->change();

            $table->string('brand_name', 255)->nullable()->change();
            $table->string('brand_slug', 255)->nullable()->change();
        });
    }
};
