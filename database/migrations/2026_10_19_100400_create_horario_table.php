<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horario', function (Blueprint $table) {
            $table->id('id_horario');

            // Cambio deliberado frente al esquema anterior: al borrar un grupo, sus horarios se van con él.
            $table->foreignId('id_grupo')->constrained('grupo', 'id_grupo')->cascadeOnDelete();
            $table->foreignId('id_dia')->constrained('dia_semana', 'id_dia');
            $table->time('hora_inicio');
            $table->time('hora_fin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horario');
    }
};
