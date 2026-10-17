<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarUsuarioRequest;
use App\Models\Alumno;
use App\Models\Carrera;
use App\Models\CoordinadorDepartamento;
use App\Models\Departamento;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $auth = $request->user();

        $consulta = User::query()
            ->with(['roles', 'alumno.carrera', 'instructor.departamento', 'coordinacion.departamento'])
            ->when($request->filled('buscar'), function ($q) use ($request) {
                $texto = '%'.$request->string('buscar')->trim().'%';
                $q->where(fn ($w) => $w->where('nombre', 'like', $texto)
                    ->orWhere('apellido_paterno', 'like', $texto)
                    ->orWhere('apellido_materno', 'like', $texto)
                    ->orWhere('email', 'like', $texto));
            })
            ->orderBy('nombre')->orderBy('apellido_paterno');

        if ($auth->esAdministrador()) {
            $consulta->when($request->filled('rol'), fn ($q) => $q->role($request->string('rol')->toString()));
        } else {
            // Acceso por departamento: solo los instructores del departamento asignado.
            $departamento = $auth->departamentoId();
            abort_if($departamento === null, 403, 'No tienes un departamento asignado.');

            $consulta->role('instructor')
                ->whereHas('instructor', fn ($q) => $q->where('id_departamento', $departamento));
        }

        return view('usuarios.index', [
            'usuarios' => $consulta->paginate(15)->withQueryString(),
            'roles' => array_keys(User::TIPO_POR_ROL),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('usuarios.create', $this->catalogos());
    }

    public function store(GuardarUsuarioRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $rol = $datos['rol'];

        $usuario = DB::transaction(function () use ($datos, $rol) {
            $usuario = User::create([
                'nombre' => $datos['nombre'],
                'apellido_paterno' => $datos['apellido_paterno'],
                'apellido_materno' => $datos['apellido_materno'] ?? null,
                'email' => $datos['email'],
                'telefono' => $datos['telefono'] ?? null,
                'contrasena' => $datos['contrasena'],
                'tipo_usuario' => User::TIPO_POR_ROL[$rol],
                'activo' => $datos['activo'] ?? true,
            ]);

            $usuario->assignRole($rol);
            $this->guardarPerfil($usuario, $rol, $datos);

            return $usuario;
        });

        return redirect()->route('usuarios.show', $usuario)
            ->with('exito', 'Usuario creado correctamente.');
    }

    public function show(User $usuario): View
    {
        Gate::authorize('view', $usuario);

        $usuario->load(['roles', 'alumno.carrera', 'instructor.departamento', 'coordinacion.departamento']);

        return view('usuarios.show', ['usuario' => $usuario]);
    }

    public function edit(User $usuario): View
    {
        Gate::authorize('update', $usuario);

        $usuario->load(['roles', 'alumno', 'instructor', 'coordinacion']);

        return view('usuarios.edit', ['usuario' => $usuario] + $this->catalogos());
    }

    public function update(GuardarUsuarioRequest $request, User $usuario): RedirectResponse
    {
        $datos = $request->validated();

        DB::transaction(function () use ($request, $usuario, $datos) {
            $cambios = [
                'nombre' => $datos['nombre'],
                'apellido_paterno' => $datos['apellido_paterno'],
                'apellido_materno' => $datos['apellido_materno'] ?? null,
                'email' => $datos['email'],
                'telefono' => $datos['telefono'] ?? null,
            ];

            if (! empty($datos['contrasena'])) {
                $cambios['contrasena'] = $datos['contrasena'];
            }

            // Nadie puede desactivarse a sí mismo desde la edición.
            if (! $usuario->is($request->user()) && array_key_exists('activo', $datos)) {
                $cambios['activo'] = (bool) $datos['activo'];
            }

            $usuario->update($cambios);
            $this->guardarPerfil($usuario, $request->rolEfectivo(), $datos);
        });

        return redirect()->route('usuarios.show', $usuario)
            ->with('exito', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario): RedirectResponse
    {
        Gate::authorize('delete', $usuario);

        try {
            DB::transaction(function () use ($usuario) {
                $usuario->coordinacion()->delete();
                $usuario->instructor()->delete();
                $usuario->alumno()->delete();
                $usuario->delete();
            });
        } catch (QueryException) {
            return back()->with('error',
                'No se puede eliminar: la cuenta tiene registros relacionados (inscripciones, grupos, etc.). Desactívala en su lugar.');
        }

        return redirect()->route('usuarios.index')->with('exito', 'Usuario eliminado correctamente.');
    }

    /** Activa o desactiva la cuenta. */
    public function toggle(User $usuario): RedirectResponse
    {
        Gate::authorize('toggle', $usuario);

        $usuario->update(['activo' => ! $usuario->activo]);

        return back()->with('exito', $usuario->activo ? 'Cuenta activada.' : 'Cuenta desactivada.');
    }

    private function catalogos(): array
    {
        return [
            'roles' => array_keys(User::TIPO_POR_ROL),
            'carreras' => Carrera::orderBy('nombre')->get(),
            'departamentos' => Departamento::orderBy('nombre')->get(),
        ];
    }

    /** Crea o actualiza la fila de perfil que corresponde al rol. */
    private function guardarPerfil(User $usuario, ?string $rol, array $datos): void
    {
        match ($rol) {
            'alumno' => Alumno::updateOrCreate(
                ['id_alumno' => $usuario->id],
                [
                    'num_control' => $datos['num_control'],
                    'id_carrera' => $datos['id_carrera'],
                    'semestre_cursando' => $datos['semestre_cursando'],
                ],
            ),
            'instructor' => Instructor::updateOrCreate(
                ['id_instructor' => $usuario->id],
                ['id_departamento' => $datos['id_departamento'], 'especialidad' => $datos['especialidad'] ?? null],
            ),
            'coordinador' => CoordinadorDepartamento::updateOrCreate(
                ['id_usuario' => $usuario->id],
                ['id_departamento' => $datos['id_departamento']],
            ),
            default => null,
        };
    }
}
