@props([
    'variant' => 'primary',
    'href' => null,
    'action' => null,
    'method' => 'POST',
    'confirm' => null,
    'type' => 'button',
    'size' => 'md',
])

@php
    $variants = [
        'primary' => 'bg-[#006437] text-white hover:bg-[#004d2a] focus-visible:ring-[#006437]/20 active:bg-[#004d2a] dark:bg-[#0f7a48] dark:hover:bg-[#0c6a3f]',
        'secondary' => 'border border-[#d9e1d9] text-[#647168] hover:border-[#d12a2e] hover:text-[#d12a2e] focus-visible:ring-[#d12a2e]/15 dark:border-white/10 dark:text-white/70 dark:hover:border-[#d12a2e] dark:hover:text-[#f2a3a5]',
        'link' => 'text-[#006437] hover:underline focus-visible:ring-[#006437]/40 dark:text-[#73c69b]',
        'danger' => 'text-[#a4262c] hover:underline focus-visible:ring-[#a4262c]/40 dark:text-[#f2a3a5]',
        'warning' => 'text-[#8a5a00] hover:underline focus-visible:ring-[#8a5a00]/40 dark:text-[#e8c07a]',
        'info' => 'text-[#1d4f91] hover:underline focus-visible:ring-[#1d4f91]/40 dark:text-[#9ec5f0]',
    ];

    $sizes = [
        'md' => 'h-11 rounded-lg px-5 text-sm font-semibold',
        'sm' => 'h-9 rounded-md px-3 text-xs font-semibold',
        'link' => 'text-xs font-semibold',
    ];
@endphp

@if ($action)
    <form method="POST" action="{{ $action }}" class="inline">
        @csrf
        @method(strtoupper($method))
        <button
            type="submit"
            @if ($confirm) onclick="return confirm({{ Js::from($confirm) }})" @endif
            {{ $attributes->class(['inline-flex items-center justify-center gap-1.5', $sizes[$size] ?? $sizes['md'], $variants[$variant] ?? $variants['primary']]) }}
        >
            {{ $slot }}
        </button>
    </form>
@elseif ($href)
    <a href="{{ $href }}" {{ $attributes->class(['inline-flex items-center justify-center gap-1.5', $sizes[$size] ?? $sizes['md'], $variants[$variant] ?? $variants['primary']]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class(['inline-flex items-center justify-center gap-1.5', $sizes[$size] ?? $sizes['md'], $variants[$variant] ?? $variants['primary']]) }}>
        {{ $slot }}
    </button>
@endif
