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
        Schema::create('users_validate_activities', function (Blueprint $table) {
            $table->bigIncrements('id_aux_activity')->primary();
            $table->integer('id_actividad');
            $table->integer('id_user');
            $table->boolean('validada');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_validate_activities');
    }
};
