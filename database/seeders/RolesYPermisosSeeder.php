<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Roles y permisos (Sprint 1) más los de catálogos y semestres (Sprint 2).
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
        ];

        foreach ($permisos as $nombre) {
            Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
        }

        $porRol = [
            'administrador' => $permisos,
            // Ve a los instructores de su departamento (el alcance lo aplica UserPolicy).
            'coordinador' => ['usuarios.ver'],
            'instructor' => [],
            'alumno' => [],
        ];

        foreach ($porRol as $nombre => $permisosDelRol) {
            $rol = Role::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
            $rol->givePermissionTo($permisosDelRol);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
