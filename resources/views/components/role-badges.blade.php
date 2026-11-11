@props(['roles'])

<span {{ $attributes->class('flex flex-wrap gap-1.5') }}>
    @forelse ($roles as $role)
        <span class="border border-[#006437]/20 bg-[#006437]/5 px-2.5 py-1 text-[11px] font-bold tracking-[0.12em] text-[#006437] uppercase dark:border-[#73c69b]/25 dark:bg-[#73c69b]/10 dark:text-[#73c69b]">
            {{ $role->label }}
        </span>
    @empty
        <span class="text-xs text-[#647168] dark:text-white/55">sem perfil</span>
    @endforelse
</span>
