<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupo', function (Blueprint $table) {
            $table->id('id_grupo');
            $table->foreignId('id_actividad')->constrained('actividad_complementaria', 'id_actividad');
            $table->foreignId('id_semestre')->constrained('semestre', 'id_semestre');

            // Un grupo puede quedar sin instructor; si se borra al instructor, el grupo sobrevive.
            $table->foreignId('id_instructor')->nullable()->constrained('instructor', 'id_instructor')->nullOnDelete();

            // Obligatoria solo en modalidad presencial o híbrida (se valida en el Request).
            $table->foreignId('id_ubicacion')->nullable()->constrained('ubicacion', 'id_ubicacion');

            $table->string('grupo', 10)->comment('Nombre del grupo: A, B…');
            $table->integer('cupo_minimo')->default(1);
            $table->integer('cupo_maximo');
            $table->integer('cupo_ocupado')->default(0)->comment('Lo actualiza el Sprint 4 al inscribir; no se captura');
            $table->enum('modalidad', ['presencial', 'virtual', 'hibrida']);
            $table->text('materiales_requeridos')->nullable();
            $table->enum('estatus', ['abierta', 'cerrada', 'cancelada', 'finalizada'])->default('abierta');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->timestamps();

            // El nombre del grupo no se repite dentro de la misma actividad.
            $table->unique(['id_actividad', 'grupo'], 'grupo_actividad_nombre_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo');
    }
};
