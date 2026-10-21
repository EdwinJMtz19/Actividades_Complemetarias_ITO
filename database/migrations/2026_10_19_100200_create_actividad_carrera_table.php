<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividad_carrera', function (Blueprint $table) {
            $table->foreignId('id_actividad')->constrained('actividad_complementaria', 'id_actividad')->cascadeOnDelete();
            $table->foreignId('id_carrera')->constrained('carrera', 'id_carrera');

            $table->primary(['id_actividad', 'id_carrera']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividad_carrera');
    }
};
