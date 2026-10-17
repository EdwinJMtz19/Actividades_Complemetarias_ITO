<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructor', function (Blueprint $table) {
            $table->unsignedBigInteger('id_instructor')->primary();
            $table->unsignedBigInteger('id_departamento');
            $table->string('especialidad', 100)->nullable();
            $table->timestamps();

            $table->foreign('id_instructor')->references('id')->on('USUARIO');
            $table->foreign('id_departamento')->references('id_departamento')->on('departamento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor');
    }
};
