<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividad_complementaria', function (Blueprint $table) {
            $table->id('id_actividad');
            $table->string('nombre', 150)->unique();
            $table->text('descripcion')->nullable();

            // Se conserva por fidelidad al modelo E-R. No existe tabla `categoria`: queda en NULL
            // y, si algún día se agrega el catálogo, se engancha aquí.
            $table->unsignedBigInteger('id_categoria')->nullable();

            $table->foreignId('id_departamento')->constrained('departamento', 'id_departamento');
            $table->foreignId('id_semestre')->nullable()->constrained('semestre', 'id_semestre')->cascadeOnDelete();
            $table->text('requisitos')->nullable();
            $table->string('nivel_actividad', 50)->nullable();
            $table->boolean('disponible')->default(true);
            $table->integer('creditos');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividad_complementaria');
    }
};
