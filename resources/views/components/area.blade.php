@props(['name', 'label', 'value' => null, 'rows' => 3, 'ayuda' => null, 'id' => null, 'required' => false])
@php $id ??= $name; @endphp
<div>
    <label for="{{ $id }}" class="block text-sm font-medium text-slate-700">
        {{ $label }} @if ($required)<span class="text-red-600">*</span>@endif
    </label>
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @required($required)
              @if ($errors->has($name)) aria-invalid="true" @endif
              {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500']) }}>{{ old($name, $value) }}</textarea>
    @if ($ayuda)<p class="mt-1 text-xs text-slate-500">{{ $ayuda }}</p>@endif
    @error($name)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
