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
        Schema::create('activities', function (Blueprint $table) {
            $table->bigIncrements('id_actividad')->primary();
            $table->string('nombre_actividad', 50);
            $table->text('descripcion');
            $table->string('tipo_actividad', 40);
            $table->date('fecha_actividad');
            $table->time('horas_actividad');
            $table->timestamps();
            $table->integer('id_usuario_asignado');
            $table->string('agregado_por', 50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
