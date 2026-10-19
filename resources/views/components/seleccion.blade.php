@props(['name', 'label', 'opciones' => [], 'value' => null, 'required' => false, 'vacio' => 'Selecciona una opción', 'id' => null, 'ayuda' => null])
@php
    $id ??= $name;
    $errorId = $id.'-error';
    $ayudaId = $id.'-ayuda';
    $describedBy = collect([$ayuda ? $ayudaId : null, $errors->has($name) ? $errorId : null])->filter()->implode(' ');
@endphp
<div>
    <label for="{{ $id }}" class="block text-sm font-medium text-slate-700">
        {{ $label }} @if ($required)<span class="text-red-600">*</span>@endif
    </label>
    <select id="{{ $id }}" name="{{ $name }}" @required($required)
            @if ($errors->has($name)) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500']) }}>
        @if ($vacio !== null)<option value="">{{ $vacio }}</option>@endif
        @foreach ($opciones as $clave => $texto)
            <option value="{{ $clave }}" @selected((string) old($name, $value) === (string) $clave)>{{ $texto }}</option>
        @endforeach
    </select>
    @if ($ayuda)<p id="{{ $ayudaId }}" class="mt-1 text-xs text-slate-500">{{ $ayuda }}</p>@endif
    @error($name)<p id="{{ $errorId }}" class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
