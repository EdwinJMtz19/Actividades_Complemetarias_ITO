<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Clave del error de autenticación. No es un campo del formulario: existe para
     * que la vista muestre el fallo en la banda de mensajes y no bajo «Correo electrónico».
     */
    public const ERROR_CREDENCIALES = 'credenciales';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'contrasena' => ['required', 'string'],
        ];
    }

    /** Intenta iniciar sesión (máx. 5 intentos fallidos por correo + IP). */
    public function authenticate(): void
    {
        $this->asegurarQueNoEstaBloqueado();

        $credenciales = [
            'email' => $this->input('email'),
            'password' => $this->input('contrasena'),
            'activo' => true,
        ];

        if (! Auth::attempt($credenciales, $this->boolean('remember'))) {
            // Mensaje distinto solo si la contraseña es correcta pero la cuenta está desactivada.
            $usuario = User::where('email', $this->input('email'))->first();

            if ($usuario && ! $usuario->activo && Hash::check($this->input('contrasena'), $usuario->contrasena)) {
                throw ValidationException::withMessages([
                    self::ERROR_CREDENCIALES => 'Tu cuenta está desactivada. Contacta al administrador.',
                ]);
            }

            RateLimiter::hit($this->claveDeBloqueo());

            throw ValidationException::withMessages([self::ERROR_CREDENCIALES => trans('auth.failed')]);
        }

        RateLimiter::clear($this->claveDeBloqueo());
    }

    private function asegurarQueNoEstaBloqueado(): void
    {
        if (! RateLimiter::tooManyAttempts($this->claveDeBloqueo(), 5)) {
            return;
        }

        event(new Lockout($this));

        $segundos = RateLimiter::availableIn($this->claveDeBloqueo());

        // El bloqueo no es un error del campo de correo: viaja en su propia clave
        // para que la vista lo muestre en la banda de mensajes y no bajo el campo.
        throw ValidationException::withMessages([
            self::ERROR_CREDENCIALES => trans('auth.throttle', ['seconds' => $segundos, 'minutes' => ceil($segundos / 60)]),
        ]);
    }

    private function claveDeBloqueo(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}
