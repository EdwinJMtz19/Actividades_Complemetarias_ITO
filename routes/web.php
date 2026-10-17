<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistroController;
use App\Http\Controllers\CarreraController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\SemestreController;
use App\Http\Controllers\UbicacionController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

// ── Invitados ────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:20,1');

    Route::get('registro', [RegistroController::class, 'create'])->name('registro');
    Route::post('registro', [RegistroController::class, 'store'])->middleware('throttle:10,1');
});

// ── Sesión iniciada ──────────────────────────────────────────────────
Route::middleware(['auth', 'usuario.activo'])->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('panel', PanelController::class)->name('panel');

    // Módulos limitados por rol/permiso y, para coordinadores, por departamento asignado.
    Route::middleware('departamento.asignado')->group(function () {
        Route::patch('usuarios/{usuario}/estado', [UsuarioController::class, 'toggle'])->name('usuarios.toggle');
        Route::resource('usuarios', UsuarioController::class)->parameters(['usuarios' => 'usuario']);

        Route::get('roles', [RolController::class, 'index'])
            ->middleware('permission:roles.ver')->name('roles.index');
        Route::put('roles/{role}', [RolController::class, 'update'])
            ->middleware('permission:roles.editar')->name('roles.update');

        // Sprint 2 · Catálogos base (administrador). Un permiso por acción.
        Route::resource('departamentos', DepartamentoController::class)
            ->except('show')->parameters(['departamentos' => 'departamento'])
            ->middlewareFor('index', 'permission:departamentos.ver')
            ->middlewareFor(['create', 'store'], 'permission:departamentos.crear')
            ->middlewareFor(['edit', 'update'], 'permission:departamentos.editar')
            ->middlewareFor('destroy', 'permission:departamentos.eliminar');

        Route::resource('carreras', CarreraController::class)
            ->except('show')->parameters(['carreras' => 'carrera'])
            ->middlewareFor('index', 'permission:carreras.ver')
            ->middlewareFor(['create', 'store'], 'permission:carreras.crear')
            ->middlewareFor(['edit', 'update'], 'permission:carreras.editar')
            ->middlewareFor('destroy', 'permission:carreras.eliminar');

        Route::resource('ubicaciones', UbicacionController::class)
            ->except('show')->parameters(['ubicaciones' => 'ubicacion'])
            ->middlewareFor('index', 'permission:ubicaciones.ver')
            ->middlewareFor(['create', 'store'], 'permission:ubicaciones.crear')
            ->middlewareFor(['edit', 'update'], 'permission:ubicaciones.editar')
            ->middlewareFor('destroy', 'permission:ubicaciones.eliminar');

        // Los semestres no se eliminan (integridad de datos): no hay ruta destroy.
        Route::resource('semestres', SemestreController::class)
            ->except('destroy')->parameters(['semestres' => 'semestre'])
            ->middlewareFor(['index', 'show'], 'permission:semestres.ver')
            ->middlewareFor(['create', 'store'], 'permission:semestres.crear')
            ->middlewareFor(['edit', 'update'], 'permission:semestres.editar');
    });
});
