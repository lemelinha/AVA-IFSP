@props([
    'ariaLabel',
    'columns' => [],
    'emptyMessage' => null,
    'isEmpty' => false,
    'minWidth' => '640px',
])

@php
    $normalizedColumns = collect($columns)->map(function ($column) {
        if (is_array($column)) {
            return [
                'label' => $column['label'] ?? '',
                'align' => $column['align'] ?? 'left',
            ];
        }

        return ['label' => $column, 'align' => 'left'];
    })->all();
@endphp

<section aria-label="{{ $ariaLabel }}" class="overflow-hidden rounded-2xl border border-[#d9e1d9] bg-white dark:border-white/10 dark:bg-[#102319]">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left" style="min-width: {{ $minWidth }};">
            <caption class="sr-only">{{ $ariaLabel }}</caption>
            <thead>
                <tr class="border-b border-[#eef2ee] bg-[#f7faf7] dark:border-white/10 dark:bg-white/[0.03]">
                    @foreach ($normalizedColumns as $column)
                        <th scope="col" class="px-5 py-3 text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55 @if ($column['align'] === 'right') text-right @endif">
                            {{ $column['label'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @if ($isEmpty)
                    <tr>
                        <td colspan="{{ count($normalizedColumns) }}" class="px-5 py-10 text-center text-sm text-[#647168] dark:text-white/55">
                            {{ $emptyMessage ?? 'Nenhum registro encontrado.' }}
                        </td>
                    </tr>
                @else
                    {{ $slot }}
                @endif
            </tbody>
        </table>
    </div>
</section>
