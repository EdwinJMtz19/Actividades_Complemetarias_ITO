<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Calificaciones de las inscripciones (Sprint 5, HU-20).
 *
 * Una inscripción tiene una sola calificación: `desempenio` es cualitativo
 * (malo, bueno, excelente) y determina el estatus aprobado/reprobado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificacion', function (Blueprint $table) {
            $table->id('id_calificacion');
            $table->foreignId('id_inscripcion')
                ->unique()
                ->constrained('inscripcion', 'id_inscripcion');
            $table->enum('desempenio', ['malo', 'bueno', 'excelente'])->nullable();
            $table->string('observaciones', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificacion');
    }
};
