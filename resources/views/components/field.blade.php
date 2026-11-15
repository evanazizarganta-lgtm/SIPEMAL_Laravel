@props(['name', 'label' => null, 'type' => 'text', 'icon' => null, 'value' => null, 'required' => false, 'hint' => null, 'as' => 'input', 'toggle' => false, 'id' => null, 'strength' => false])
@php
$id = $id ?? $name;
$err = $errors->first($name);
$val = $type === 'password' ? null : old($name, $value);
@endphp
<div class="field {{ $err ? 'invalid' : '' }}">
    @if($label)<label for="{{ $id }}">{{ $label }}@if($required)<span class="req" aria-hidden="true">*</span>@endif</label>@endif
    <div class="control {{ $icon ? 'has-icon' : '' }} {{ $toggle ? 'has-toggle' : '' }} {{ $as === 'textarea' ? 'ta' : '' }}">
        @if($icon)<x-icon :name="$icon"/>@endif
        @if($as === 'select')
            <select id="{{ $id }}" name="{{ $name }}" {{ $attributes }} @required($required) @if($err) aria-invalid="true" @endif>{{ $slot }}</select>
        @elseif($as === 'textarea')
            <textarea id="{{ $id }}" name="{{ $name }}" {{ $attributes->merge(['rows' => 3]) }} @required($required) @if($err) aria-invalid="true" @endif>{{ $val }}</textarea>
        @else
            <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" @if($val !== null) value="{{ $val }}" @endif {{ $attributes }} @required($required) @if($err) aria-invalid="true" aria-describedby="{{ $id }}-err" @endif>
        @endif
        @if($toggle)
            <button type="button" class="pw-toggle" data-toggle-password aria-label="Tampilkan kata sandi"><x-icon name="eye" class="icon-eye"/><x-icon name="eye-off" class="icon-eye-off" style="display:none"/></button>
        @endif
    </div>
    @if($strength)<div class="strength" data-strength-for="{{ $id }}" data-s="0" aria-hidden="true"><i></i><i></i><i></i><i></i></div>@endif
    @if($err)<p class="field-error" id="{{ $id }}-err" role="alert"><x-icon name="alert" class="icon-sm"/>{{ $err }}</p>
    @elseif($hint)<p class="field-hint">{{ $hint }}</p>@endif
</div>
