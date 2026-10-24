@props(['head' => [], 'caption' => null, 'empty' => null])

<div {{ $attributes->merge(['class' => 'table-wrap']) }}>
    <table class="data-table">
        @if ($caption)
            <caption class="sr-only">{{ $caption }}</caption>
        @endif

        @if (count($head))
            <thead>
                <tr>
                    @foreach ($head as $column)
                        <th scope="col">{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif

        <tbody>
            @if ($slot->isEmpty())
                <tr>
                    <td colspan="{{ max(count($head), 1) }}" class="px-4 py-10 text-center text-sm text-cocoa/45">
                        {{ $empty ?? 'Belum ada data.' }}
                    </td>
                </tr>
            @else
                {{ $slot }}
            @endif
        </tbody>
    </table>
</div>
