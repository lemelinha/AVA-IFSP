@props([
    'name',
    'label',
    'value',
    'checked' => false,
])

@php
    $isChecked = old($name) !== null
        ? in_array((string) $value, array_map('strval', (array) old($name)), true)
        : $checked;
@endphp

<label class="flex cursor-pointer items-center gap-3 rounded-lg border border-[#d9e1d9] px-4 py-3 text-sm transition hover:border-[#006437]/40 dark:border-white/10 dark:hover:border-[#73c69b]/40">
    <input
        type="checkbox"
        name="{{ $name }}"
        value="{{ $value }}"
        @checked($isChecked)
        class="h-4 w-4 accent-[#006437] dark:accent-[#73c69b]"
    >
    <span class="font-medium text-[#172019] dark:text-white">{{ $label }}</span>
</label>
