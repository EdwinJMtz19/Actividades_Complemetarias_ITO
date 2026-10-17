<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegistroRequest;
use App\Models\Alumno;
use App\Models\Carrera;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegistroController extends Controller
{
    public function create(): View
    {
        return view('auth.registro', ['carreras' => Carrera::orderBy('nombre')->get()]);
    }

    public function store(RegistroRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        $usuario = DB::transaction(function () use ($datos) {
            $usuario = User::create([
                'nombre' => $datos['nombre'],
                'apellido_paterno' => $datos['apellido_paterno'],
                'apellido_materno' => $datos['apellido_materno'] ?? null,
                'email' => $datos['email'],
                'telefono' => $datos['telefono'] ?? null,
                'contrasena' => $datos['contrasena'],
                'tipo_usuario' => User::TIPO_POR_ROL['alumno'],
                'activo' => true,
            ]);

            // El rol siempre es "alumno": nunca se toma del formulario.
            $usuario->assignRole('alumno');

            Alumno::create([
                'id_alumno' => $usuario->id,
                'num_control' => $datos['num_control'],
                'id_carrera' => $datos['id_carrera'],
                'semestre_cursando' => $datos['semestre_cursando'],
                'creditos_acumulados' => 0,
            ]);

            return $usuario;
        });

        Auth::login($usuario);
        $request->session()->regenerate();
        $usuario->forceFill(['ultimo_acceso' => now()])->save();

        return redirect()->route('panel');
    }
}
