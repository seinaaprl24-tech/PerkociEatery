@props(['title' => null, 'description' => null, 'flush' => false])

<section {{ $attributes->merge(['class' => 'card overflow-hidden']) }}>
    @if ($title || (isset($actions) && ! $actions->isEmpty()))
        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-cocoa/10 px-5 py-4">
            <div class="min-w-0">
                @if ($title)
                    <h2 class="font-display text-[15px] font-bold leading-tight text-cocoa">{{ $title }}</h2>
                @endif

                @if ($description)
                    <p class="mt-1 text-[11px] leading-relaxed text-cocoa/50">{{ $description }}</p>
                @endif
            </div>

            @isset($actions)
                @if (! $actions->isEmpty())
                    <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
                @endif
            @endisset
        </div>
    @endif

    <div class="{{ $flush ? '' : 'px-5 py-4' }}">
        {{ $slot }}
    </div>
</section>
