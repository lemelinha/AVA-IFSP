@props([
    'name',
    'label',
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'required' => false,
])

<div>
    <label for="{{ $name }}" class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">{{ $label }}</label>
    <div class="relative mt-2">
        <select
            id="{{ $name }}"
            name="{{ $name }}"
            @if ($required) required @endif
            @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
            class="h-11 w-full appearance-none rounded-lg border border-[#d9e1d9] bg-white px-3.5 pr-9 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10 @error($name) border-[#a4262c] focus:border-[#a4262c] focus:ring-[#a4262c]/10 dark:border-[#f2a3a5] dark:focus:border-[#f2a3a5] dark:focus:ring-[#f2a3a5]/10 @enderror"
        >
            @if ($placeholder)
                <option value="" disabled @selected(old($name, $selected) === null)>{{ $placeholder }}</option>
            @endif
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) old($name, $selected) === (string) $optionValue)>{{ $optionLabel }}</option>
            @endforeach
        </select>
        <svg class="pointer-events-none absolute top-1/2 right-3.5 h-3.5 w-3.5 -translate-y-1/2 text-[#647168] dark:text-white/45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" d="M6 9l6 6 6-6" />
        </svg>
    </div>
    @error($name)
        <p id="{{ $name }}-error" class="mt-1.5 text-xs font-medium text-[#a4262c] dark:text-[#f2a3a5]">{{ $message }}</p>
    @enderror
</div>
