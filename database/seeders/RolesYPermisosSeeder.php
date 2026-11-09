<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Roles y permisos (Sprint 1), catálogos y semestres (Sprint 2), actividades y grupos (Sprint 3),
 * inscripción de alumnos (Sprint 4) y calificaciones (Sprint 5).
 * Es idempotente y solo AGREGA permisos,
 * así que se puede volver a ejecutar sin pisar lo configurado en la pantalla de roles.
 * Los sprints siguientes añaden aquí sus propios permisos.
 */
class RolesYPermisosSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permisos = [
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',
            'roles.ver',
            'roles.editar',
            // Sprint 2 · catálogos base y semestres (solo el administrador por ahora).
            'departamentos.ver',
            'departamentos.crear',
            'departamentos.editar',
            'departamentos.eliminar',
            'carreras.ver',
            'carreras.crear',
            'carreras.editar',
            'carreras.eliminar',
            'ubicaciones.ver',
            'ubicaciones.crear',
            'ubicaciones.editar',
            'ubicaciones.eliminar',
            // Los semestres no se eliminan: no existe el permiso semestres.eliminar.
            'semestres.ver',
            'semestres.crear',
            'semestres.editar',
            // Sprint 3 · actividades y grupos. Los horarios viajan dentro del formulario del grupo:
            // se editan con grupos.editar, por eso no existe un permiso horarios.*.
            'actividades.ver',
            'actividades.crear',
            'actividades.editar',
            'actividades.eliminar',
            'grupos.ver',
            'grupos.crear',
            'grupos.editar',
            'grupos.eliminar',
            // Sprint 4 · inscripción de alumnos. Ver el catálogo y apuntarse a un grupo.
            'inscripciones.ver',
            'inscripciones.crear',
            'inscripciones.baja',
            // Sprint 5 · calificaciones. El instructor registra el desempeño; el coordinador
            // consulta las de su departamento y el administrador todo.
            'calificaciones.ver',
            'calificaciones.editar',
        ];

        foreach ($permisos as $nombre) {
            Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
        }

        $porRol = [
            'administrador' => $permisos,
            // Ve a los instructores de su departamento (el alcance lo aplica UserPolicy) y administra
            // las actividades y grupos de su departamento (el alcance lo aplican ActividadPolicy y GrupoPolicy).
            'coordinador' => [
                'usuarios.ver',
                'ubicaciones.ver',
                'actividades.ver', 'actividades.crear', 'actividades.editar', 'actividades.eliminar',
                'grupos.ver', 'grupos.crear', 'grupos.editar', 'grupos.eliminar',
                'inscripciones.ver', 'inscripciones.baja',
                'calificaciones.ver',
            ],
            // Consulta y califica solo sus propios grupos (el alcance lo aplica InscripcionPolicy).
            'instructor' => ['grupos.ver', 'calificaciones.ver', 'calificaciones.editar'],
            // Consulta el catálogo de su carrera, los grupos, se inscribe (HU-17) y cancela su
            // propia inscripción (HU-19); la política comprueba que la inscripción sea suya.
            'alumno' => ['actividades.ver', 'grupos.ver', 'inscripciones.ver', 'inscripciones.crear', 'inscripciones.baja'],
        ];

        foreach ($porRol as $nombre => $permisosDelRol) {
            $rol = Role::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
            $rol->givePermissionTo($permisosDelRol);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
