<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Inscripciones de alumnos a grupos (Sprint 4).
 *
 * Regla de negocio: una sola inscripción activa por alumno. En MySQL se garantiza con una
 * columna generada (BIGINT UNSIGNED, igual que id_alumno) y un índice único; en SQLite, que
 * es el motor de las pruebas, con un índice único parcial equivalente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripcion', function (Blueprint $table) {
            $table->id('id_inscripcion');
            $table->foreignId('id_alumno')->constrained('alumno', 'id_alumno');
            $table->foreignId('id_grupo')->constrained('grupo', 'id_grupo');
            $table->dateTime('fecha_inscripcion');
            $table->enum('estatus', ['inscrito', 'cursando', 'aprobado', 'reprobado', 'dado_de_baja'])
                ->default('inscrito');
            $table->timestamps();
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE inscripcion
                ADD COLUMN unique_active_alumno BIGINT UNSIGNED AS (
                    CASE WHEN estatus IN ('inscrito', 'cursando') THEN id_alumno ELSE NULL END
                ) STORED,
                ADD UNIQUE INDEX uq_inscripcion_activa (unique_active_alumno)
            ");
        } else {
            // SQLite: mismo efecto con un índice único parcial (las pruebas corren aquí).
            DB::statement("
                CREATE UNIQUE INDEX uq_inscripcion_activa
                ON inscripcion (id_alumno)
                WHERE estatus IN ('inscrito', 'cursando')
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripcion');
    }
};
