@props(['icon' => 'inbox', 'title' => 'Belum ada data', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-14 text-center']) }}>
    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-sand text-cocoa/35">
        <span class="ms text-[28px] leading-none" aria-hidden="true">{{ $icon }}</span>
    </span>

    <p class="mt-4 text-sm font-bold text-cocoa/70">{{ $title }}</p>

    @if ($description)
        <p class="mt-1 max-w-xs text-xs leading-relaxed text-cocoa/45">{{ $description }}</p>
    @endif

    @if (! $slot->isEmpty())
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
