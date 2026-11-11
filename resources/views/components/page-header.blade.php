@props([
    'title',
    'subtitle' => null,
    'back' => null,
    'backLabel' => null,
])

<div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0">
        @if ($back)
            <a href="{{ $back }}" class="text-xs font-semibold text-[#647168] transition hover:text-[#006437] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#006437]/40 dark:text-white/65 dark:hover:text-[#73c69b]">
                ← {{ $backLabel ?? 'Voltar' }}
            </a>
        @endif

        <h1 class="{{ $back ? 'mt-3' : '' }} text-[28px] font-semibold tracking-tight text-[#172019] sm:text-3xl dark:text-white">{{ $title }}</h1>

        @if ($subtitle)
            <p class="mt-1.5 text-sm leading-6 text-[#647168] dark:text-white/65">{{ $subtitle }}</p>
        @endif
    </div>

    @if (isset($actions) && trim((string) $actions) !== '')
        <div class="flex flex-wrap items-center gap-3">{{ $actions }}</div>
    @endif
</div>
