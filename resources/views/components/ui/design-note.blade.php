@props(['title' => 'Menunggu referensi Figma'])

<div {{ $attributes->merge(['class' => 'flex items-start gap-2 rounded-xl border border-dashed border-cocoa/20 bg-sand/50 px-3.5 py-3']) }}>
    <span class="ms mt-px text-[16px] leading-none text-cocoa/35" aria-hidden="true">stacks</span>

    <div class="min-w-0">
        <p class="text-[11px] font-bold text-cocoa/60">{{ $title }}</p>

        @if (! $slot->isEmpty())
            <p class="mt-0.5 text-[11px] leading-relaxed text-cocoa/45">{{ $slot }}</p>
        @endif
    </div>
</div>
