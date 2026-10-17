<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PanelController extends Controller
{
    public function __invoke(Request $request): View
    {
        $usuario = $request->user()->load(['alumno.carrera', 'instructor.departamento', 'coordinacion.departamento']);

        return view('panel', [
            'usuario' => $usuario,
            'rol' => $usuario->getRoleNames()->first(),
        ]);
    }
}
