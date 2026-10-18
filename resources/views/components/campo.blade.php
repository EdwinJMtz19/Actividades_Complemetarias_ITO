@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false, 'ayuda' => null, 'id' => null, 'adorno' => false])
@php
    $id ??= $name;
    $errorId = $id.'-error';
    $ayudaId = $id.'-ayuda';
    $describedBy = collect([$ayuda ? $ayudaId : null, $errors->has($name) ? $errorId : null])->filter()->implode(' ');
    // El adorno (botón de mostrar contraseña) vive dentro del campo: el input cede espacio a la derecha.
    $claseInput = 'mt-1 block w-full rounded-md border border-slate-300 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500';
    $claseInput .= $adorno ? ' px-3 pr-10' : ' px-3';
@endphp
<div>
    <label for="{{ $id }}" class="block text-sm font-medium text-slate-700">
        {{ $label }} @if ($required)<span class="text-red-600">*</span>@endif
    </label>
    <div class="relative">
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
               value="{{ $type === 'password' ? '' : old($name, $value) }}"
               @required($required)
               @if ($errors->has($name)) aria-invalid="true" @endif
               @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
               {{ $attributes->merge(['class' => $claseInput]) }}>
        @if ($adorno)<span class="absolute inset-y-0 right-0 flex items-center pr-2">{{ $adorno }}</span>@endif
    </div>
    @if ($ayuda)<p id="{{ $ayudaId }}" class="mt-1 text-xs text-slate-500">{{ $ayuda }}</p>@endif
    @error($name)<p id="{{ $errorId }}" data-error-de-campo class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
