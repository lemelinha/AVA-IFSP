@props([
    'title',
    'subtitle' => null,
    'back' => null,
    'backLabel' => null,
    'action',
    'method' => 'POST',
])

<div>
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

@include('partials.flash')

<form
    method="POST"
    action="{{ $action }}"
    enctype="multipart/form-data"
    class="mt-7 max-w-2xl rounded-2xl border border-[#d9e1d9] bg-white p-6 dark:border-white/10 dark:bg-[#102319]"
>
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    {{ $slot }}

    @if (isset($actions) && trim((string) $actions) !== '')
        <div class="mt-7 flex items-center justify-end gap-3">{{ $actions }}</div>
    @endif
</form>
