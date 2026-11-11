@props([
    'name',
    'label',
    'options' => [],
    'selected' => null,
    'placeholder' => 'Digite para buscar',
    'required' => false,
])

@php
    $id = 'combobox-'.Str::uuid();
    $selectedLabel = null;
    if ($selected !== null) {
        foreach ($options as $value => $text) {
            if ((string) $value === (string) $selected) {
                $selectedLabel = $text;
                break;
            }
        }
    }
@endphp

<div class="relative" data-combobox>
    <label for="{{ $id }}-input" class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">{{ $label }}</label>

    <input type="hidden" name="{{ $name }}" value="{{ old($name, $selected) }}" data-combobox-value>

    <div class="relative mt-2">
        <input
            id="{{ $id }}-input"
            type="text"
            value="{{ old($name, $selectedLabel) }}"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            @if ($required) required @endif
            data-combobox-input
            class="h-11 w-full rounded-lg border border-[#d9e1d9] bg-white px-3.5 pr-9 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10 @error($name) border-[#a4262c] focus:border-[#a4262c] focus:ring-[#a4262c]/10 dark:border-[#f2a3a5] dark:focus:border-[#f2a3a5] dark:focus:ring-[#f2a3a5]/10 @enderror"
        >

        <svg class="pointer-events-none absolute top-1/2 right-3.5 h-3.5 w-3.5 -translate-y-1/2 text-[#647168] dark:text-white/45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" d="M6 9l6 6 6-6" />
        </svg>

        <ul
            data-combobox-list
            class="absolute z-20 mt-1 hidden max-h-60 w-full overflow-auto rounded-lg border border-[#d9e1d9] bg-white py-1 text-sm shadow-lg dark:border-white/10 dark:bg-[#102319]"
        >
            @foreach ($options as $value => $text)
                <li
                    data-value="{{ $value }}"
                    data-label="{{ $text }}"
                    class="cursor-pointer px-3.5 py-2 text-[#172019] hover:bg-[#006437]/5 dark:text-white dark:hover:bg-[#73c69b]/10"
                >
                    {{ $text }}
                </li>
            @endforeach
            <li data-empty class="hidden px-3.5 py-2 text-[#647168] dark:text-white/55">Nenhum resultado.</li>
        </ul>
    </div>

    @error($name)
        <p class="mt-1.5 text-xs font-medium text-[#a4262c] dark:text-[#f2a3a5]">{{ $message }}</p>
    @enderror

<script>
(function () {
    var script = document.currentScript;
    var root = script ? script.closest('[data-combobox]') : null;
    if (! root) { return; }

    var input = root.querySelector('[data-combobox-input]');
    var hidden = root.querySelector('[data-combobox-value]');
    var list = root.querySelector('[data-combobox-list]');
    var items = Array.prototype.slice.call(list.querySelectorAll('li[data-value]'));
    var emptyItem = list.querySelector('li[data-empty]');
    var activeIndex = -1;

    function show() { list.classList.remove('hidden'); }
    function hide() { list.classList.add('hidden'); activeIndex = -1; resetActive(); }

    function resetActive() {
        items.forEach(function (item) { item.classList.remove('bg-[#006437]/10', 'dark:bg-[#73c69b]/15'); });
    }

    function filter(term) {
        var lower = (term || '').toLowerCase();
        var visible = 0;
        items.forEach(function (item) {
            var label = (item.getAttribute('data-label') || '').toLowerCase();
            var match = ! lower || label.indexOf(lower) !== -1;
            item.classList.toggle('hidden', ! match);
            if (match) { visible++; }
        });
        if (emptyItem) {
            emptyItem.classList.toggle('hidden', visible > 0);
        }
    }

    function select(item) {
        if (! item) { return; }
        hidden.value = item.getAttribute('data-value');
        input.value = item.getAttribute('data-label');
        hide();
    }

    input.addEventListener('focus', function () {
        filter(input.value);
        show();
    });

    input.addEventListener('input', function () {
        hidden.value = '';
        filter(input.value);
        show();
    });

    input.addEventListener('keydown', function (event) {
        var visibleItems = items.filter(function (item) { return ! item.classList.contains('hidden'); });
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            if (list.classList.contains('hidden')) { show(); return; }
            activeIndex = Math.min(activeIndex + 1, visibleItems.length - 1);
            resetActive();
            if (visibleItems[activeIndex]) { visibleItems[activeIndex].classList.add('bg-[#006437]/10', 'dark:bg-[#73c69b]/15'); }
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            activeIndex = Math.max(activeIndex - 1, 0);
            resetActive();
            if (visibleItems[activeIndex]) { visibleItems[activeIndex].classList.add('bg-[#006437]/10', 'dark:bg-[#73c69b]/15'); }
        } else if (event.key === 'Enter') {
            event.preventDefault();
            if (activeIndex >= 0 && visibleItems[activeIndex]) {
                select(visibleItems[activeIndex]);
            } else if (visibleItems.length === 1) {
                select(visibleItems[0]);
            }
        } else if (event.key === 'Escape') {
            hide();
        }
    });

    items.forEach(function (item) {
        item.addEventListener('mousedown', function (event) {
            event.preventDefault();
            select(item);
        });
    });

    document.addEventListener('click', function (event) {
        if (! root.contains(event.target)) { hide(); }
    });
})();
</script>
</div>
