@props(['id', 'title' => null, 'description' => null, 'closeLabel' => 'Tutup'])

<dialog id="{{ $id }}" data-modal class="modal">
    <div class="flex items-start justify-between gap-4 border-b border-cocoa/10 px-5 py-4">
        <div class="min-w-0">
            @if ($title)
                <h2 class="font-display text-[17px] font-bold leading-tight text-cocoa">{{ $title }}</h2>
            @endif

            @if ($description)
                <p class="mt-1 text-xs leading-relaxed text-cocoa/55">{{ $description }}</p>
            @endif
        </div>

        <button type="button" data-modal-close
            class="btn-icon -mr-1.5 -mt-1" aria-label="{{ $closeLabel }}">
            <span class="ms text-[18px] leading-none" aria-hidden="true">close</span>
        </button>
    </div>

    @if (! $slot->isEmpty())
        <div class="px-5 py-4 text-sm leading-relaxed text-cocoa/75">{{ $slot }}</div>
    @endif

    @isset($footer)
        @if (! $footer->isEmpty())
            <div class="flex flex-wrap items-center justify-end gap-2 border-t border-cocoa/10 bg-cream/60 px-5 py-3">
                {{ $footer }}
            </div>
        @endif
    @endisset
</dialog>
