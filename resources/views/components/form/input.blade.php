@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'autocomplete' => null,
])

<div>
    <label for="{{ $name }}" class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">{{ $label }}</label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($required) required @endif
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
        class="mt-2 h-11 w-full rounded-lg border border-[#d9e1d9] bg-white px-3.5 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10 @error($name) border-[#a4262c] focus:border-[#a4262c] focus:ring-[#a4262c]/10 dark:border-[#f2a3a5] dark:focus:border-[#f2a3a5] dark:focus:ring-[#f2a3a5]/10 @enderror"
    >
    @error($name)
        <p id="{{ $name }}-error" class="mt-1.5 text-xs font-medium text-[#a4262c] dark:text-[#f2a3a5]">{{ $message }}</p>
    @enderror
</div>
