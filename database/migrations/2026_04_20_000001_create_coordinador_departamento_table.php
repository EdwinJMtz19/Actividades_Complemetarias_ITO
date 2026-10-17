<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coordinador_departamento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario')->unique()->comment('FK a USUARIO (debe tener rol coordinador)');
            $table->unsignedBigInteger('id_departamento')->unique()->comment('Cada departamento solo tiene un coordinador');
            $table->timestamps();

            $table->foreign('id_usuario')->references('id')->on('USUARIO')->onDelete('cascade');
            $table->foreign('id_departamento')->references('id_departamento')->on('departamento')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coordinador_departamento');
    }
};
