<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Esquema final: incluye num_control (antes se movía desde USUARIO).
 * CORRECCIÓN: se agrega la llave foránea id_carrera -> carrera,
 * que no existía en el proyecto original.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumno', function (Blueprint $table) {
            $table->unsignedBigInteger('id_alumno')->primary();
            $table->string('num_control', 9)->nullable()->unique();
            $table->unsignedBigInteger('id_carrera');
            $table->integer('semestre_cursando');
            $table->integer('creditos_acumulados')->default(0);
            $table->timestamps();

            $table->foreign('id_alumno')->references('id')->on('USUARIO')->onDelete('cascade');
            $table->foreign('id_carrera')->references('id_carrera')->on('carrera');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumno');
    }
};
