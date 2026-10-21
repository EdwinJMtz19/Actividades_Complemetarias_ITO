<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerificarHorariosRequest;
use App\Models\Grupo;
use App\Models\Semestre;
use App\Services\ServicioConflictos;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/**
 * Aviso de conflictos en vivo (HU-16). No guarda nada: responde en JSON qué choca con lo que se está
 * capturando. La decisión final sigue siendo de GuardarGrupoRequest al guardar, con el mismo servicio.
 */
class HorarioController extends Controller
{
    public function verificar(VerificarHorariosRequest $request, ServicioConflictos $servicio): JsonResponse
    {
        Semestre::cerrarVencidos();

        $grupo = $request->filled('id_grupo') ? Grupo::findOrFail($request->input('id_grupo')) : null;

        if ($grupo !== null) {
            Gate::authorize('update', $grupo);
        }

        $semestre = $grupo?->semestre ?? Semestre::activo();

        if ($semestre === null) {
            return response()->json([
                'hayConflicto' => false,
                'conflictos' => [],
                'aviso' => 'No hay un periodo escolar activo: no se pueden crear grupos.',
            ]);
        }

        $conflictos = $servicio->verificar(
            [
                'id_semestre' => $semestre->id_semestre,
                'id_instructor' => $request->input('id_instructor'),
                'id_ubicacion' => $request->input('id_ubicacion'),
                'modalidad' => $request->input('modalidad'),
                'cupo_maximo' => $request->input('cupo_maximo'),
                'estatus' => $request->input('estatus', 'abierta'),
            ],
            $request->input('horarios', []),
            $grupo?->id_grupo,
        );

        return response()->json([
            'hayConflicto' => $conflictos !== [],
            'conflictos' => $conflictos,
        ]);
    }
}
