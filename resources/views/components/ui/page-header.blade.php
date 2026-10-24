@props(['title', 'subtitle' => null, 'eyebrow' => null])

<div {{ $attributes->merge(['class' => 'page-header']) }}>
    <div class="min-w-0">
        @if ($eyebrow)
            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-olive">{{ $eyebrow }}</p>
        @endif

        <h1 class="mt-1 font-display text-[22px] font-bold leading-tight text-cocoa sm:text-[26px]">{{ $title }}</h1>

        @if ($subtitle)
            <p class="mt-1 text-sm leading-relaxed text-cocoa/55">{{ $subtitle }}</p>
        @endif
    </div>

    @if (! $slot->isEmpty())
        <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif
</div>
