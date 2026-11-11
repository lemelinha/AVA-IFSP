@props(['status', 'label' => null])

@php
    $variants = [
        'active' => [
            'label' => 'Ativo',
            'classes' => 'border-[#c4e2cc] bg-[#e7f4ea] text-[#1e6b34] dark:border-[#8fd0a3]/30 dark:bg-[#e7f4ea]/10 dark:text-[#8fd0a3]',
        ],
        'blocked' => [
            'label' => 'Bloqueado',
            'classes' => 'border-[#f3c9c9] bg-[#fdeaea] text-[#a4262c] dark:border-[#f2a3a5]/30 dark:bg-[#fdeaea]/10 dark:text-[#f2a3a5]',
        ],
        'pending' => [
            'label' => 'Pendente',
            'classes' => 'border-[#f0dcb0] bg-[#fdf3e0] text-[#8a5a00] dark:border-[#e8c07a]/30 dark:bg-[#fdf3e0]/10 dark:text-[#e8c07a]',
        ],
        'sent' => [
            'label' => 'Enviada',
            'classes' => 'border-[#c8dcf4] bg-[#e8f0fb] text-[#1d4f91] dark:border-[#9ec5f0]/30 dark:bg-[#e8f0fb]/10 dark:text-[#9ec5f0]',
        ],
        'in_correction' => [
            'label' => 'Em correção',
            'classes' => 'border-[#f0dcb0] bg-[#fdf3e0] text-[#8a5a00] dark:border-[#e8c07a]/30 dark:bg-[#fdf3e0]/10 dark:text-[#e8c07a]',
        ],
        'corrected' => [
            'label' => 'Corrigida',
            'classes' => 'border-[#c4e2cc] bg-[#e7f4ea] text-[#1e6b34] dark:border-[#8fd0a3]/30 dark:bg-[#e7f4ea]/10 dark:text-[#8fd0a3]',
        ],
        'concluded' => [
            'label' => 'Concluída',
            'classes' => 'border-[#172019] bg-[#172019] text-white dark:border-white dark:bg-white dark:text-[#172019]',
        ],
    ];

    $variant = $variants[$status] ?? [
        'label' => $status,
        'classes' => 'border-[#d9e1d9] bg-[#f7faf7] text-[#647168] dark:border-white/10 dark:bg-white/5 dark:text-white/65',
    ];
@endphp

<span {{ $attributes->class(['inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-semibold', $variant['classes']]) }}>
    {{ $label ?? $variant['label'] }}
</span>
