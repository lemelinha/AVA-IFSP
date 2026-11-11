@props([
    'action' => null,
    'placeholder' => 'Buscar por nome, prontuário ou código',
    'filters' => [],
    'values' => [],
    'buttonLabel' => 'Filtrar',
])

@php
    $hasFilters = collect($values)->contains(fn ($value) => $value !== null && $value !== '');
@endphp

<form method="GET" action="{{ $action ?? url()->current() }}" class="mt-5 rounded-2xl border border-[#d9e1d9] bg-white p-4 dark:border-white/10 dark:bg-[#102319]">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <div class="relative flex-1">
            <label for="f-busca" class="sr-only">Buscar</label>
            <svg class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-[#647168] dark:text-white/45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <circle cx="11" cy="11" r="7" />
                <path stroke-linecap="round" d="M20 20l-3.6-3.6" />
            </svg>
            <input
                id="f-busca"
                type="search"
                name="q"
                value="{{ $values['q'] ?? '' }}"
                placeholder="{{ $placeholder }}"
                autocomplete="off"
                class="h-11 w-full rounded-lg border border-[#d9e1d9] bg-white pr-4 pl-10 text-sm text-[#172019] placeholder:text-[#647168] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-white/40 dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10"
            >
        </div>

        @foreach ($filters as $filter)
            @php
                $name = $filter['name'];
                $label = $filter['label'];
                $options = $filter['options'] ?? [];
                $placeholder = $filter['placeholder'] ?? $label;
            @endphp

            <div class="relative">
                <label for="f-{{ $name }}" class="sr-only">{{ $label }}</label>
                <select
                    id="f-{{ $name }}"
                    name="{{ $name }}"
                    class="h-11 w-full appearance-none rounded-lg border border-[#d9e1d9] bg-white px-3.5 pr-9 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 lg:w-auto dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10"
                >
                    <option value="">{{ $placeholder }}</option>
                    @foreach ($options as $value => $text)
                        <option value="{{ $value }}" @selected((string) ($values[$name] ?? '') === (string) $value)>{{ $text }}</option>
                    @endforeach
                </select>
                <svg class="pointer-events-none absolute top-1/2 right-3.5 h-3.5 w-3.5 -translate-y-1/2 text-[#647168] dark:text-white/45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="M6 9l6 6 6-6" />
                </svg>
            </div>
        @endforeach

        <div class="flex items-center gap-2">
            <button type="submit" class="h-11 rounded-lg bg-[#006437] px-5 text-sm font-semibold text-white transition hover:bg-[#004d2a] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#006437]/20 active:bg-[#004d2a] dark:bg-[#0f7a48] dark:hover:bg-[#0c6a3f]">
                {{ $buttonLabel }}
            </button>

            @if ($hasFilters)
                <a href="{{ $action ?? url()->current() }}" class="h-11 inline-flex items-center rounded-lg border border-[#d9e1d9] px-4 text-sm font-semibold text-[#647168] transition hover:border-[#d12a2e] hover:text-[#d12a2e] dark:border-white/10 dark:text-white/70 dark:hover:border-[#d12a2e] dark:hover:text-[#f2a3a5]">
                    Limpar
                </a>
            @endif
        </div>
    </div>
</form>
