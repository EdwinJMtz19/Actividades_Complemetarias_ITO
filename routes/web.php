<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistroController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\RolController;
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
    });
});
