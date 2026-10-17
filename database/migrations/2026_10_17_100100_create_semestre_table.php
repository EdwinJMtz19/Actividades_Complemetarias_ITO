<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Esquema final de semestre: reúne las columnas que en el proyecto anterior
 * llegaron en tres migraciones (status y las horas de inscripción).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semestre', function (Blueprint $table) {
            $table->id('id_semestre');
            $table->integer('año');
            $table->integer('periodo')->comment('1 = enero-junio, 2 = agosto-diciembre');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->date('fecha_inicio_inscripciones');
            $table->time('hora_inicio_inscripciones')->default('08:00');
            $table->date('fecha_fin_inscripciones');
            $table->time('hora_fin_inscripciones')->default('20:00');
            $table->string('status', 10)->default('inactivo')->comment('activo | inactivo');
            $table->timestamps();

            // Un solo semestre por año y periodo.
            $table->unique(['año', 'periodo'], 'semestre_anio_periodo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semestre');
    }
};
