@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'error' => null,
    'icon' => null,
    'required' => false,
])

@php
    $id = $attributes->get('id', 'field-'.str_replace(['[', ']', '.'], '-', $name));
    $hasError = filled($error);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    @if ($label)
        <label for="{{ $id }}" class="field-label">
            {{ $label }}@if ($required)<span class="ml-0.5 text-danger" aria-hidden="true">*</span>@endif
        </label>
    @endif

    @if (! $slot->isEmpty())
        {{ $slot }}
    @else
        <div class="relative">
            @if ($icon)
                <span class="ms pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[18px] leading-none text-cocoa/40"
                    aria-hidden="true">{{ $icon }}</span>
            @endif

            <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
                @if ($value !== null) value="{{ $value }}" @endif
                @if ($placeholder) placeholder="{{ $placeholder }}" @endif
                @if ($required) required @endif
                @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
                {{ $attributes->except(['class', 'id', 'name', 'type'])->merge([
                    'class' => 'field '.($icon ? 'pl-11 ' : '').($hasError ? 'field-invalid' : ''),
                ]) }}>
        </div>
    @endif

    @if ($hasError)
        <p id="{{ $id }}-error" class="field-error">
            <span class="ms text-[14px] leading-none" aria-hidden="true">error</span>
            {{ $error }}
        </p>
    @elseif ($hint)
        <p class="field-hint">{{ $hint }}</p>
    @endif
</div>
