@props(['activity', 'fileRoute' => 'aluno.activity.answer', 'heading' => 'Resolução do aluno', 'routeParams' => null])

<section class="rounded-xl border border-[#d9e1d9] bg-[#f7faf7] p-4 dark:border-white/10 dark:bg-white/[0.03]">
    <p class="text-xs font-semibold text-[#172019] dark:text-white">{{ $heading }}</p>
    <p class="mt-0.5 text-xs text-[#647168] dark:text-white/55">
        Enviada em {{ $activity->answered_at?->format('d/m/Y H:i') ?? '—' }}
    </p>

    @if ($activity->answer_type === 'pdf' && $activity->url_answer)
        <a href="{{ route($fileRoute, $routeParams ?? $activity) }}" class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-[#006437] hover:underline dark:text-[#73c69b]">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0 4-4m-4 4-4-4M4 20h16" />
            </svg>
            Baixar PDF enviado →
        </a>
    @elseif ($activity->answer_type === 'link' && $activity->url_answer)
        <a href="{{ $activity->url_answer }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex break-all text-xs font-semibold text-[#006437] hover:underline dark:text-[#73c69b]">
            {{ $activity->url_answer }}
        </a>
    @elseif ($activity->answer_type === 'text' && $activity->answer_text)
        <p class="mt-2 text-sm leading-6 whitespace-pre-line text-[#172019] dark:text-white">{{ $activity->answer_text }}</p>
    @endif

    @if ($activity->grade !== null)
        <p class="mt-3 text-xs font-semibold text-[#1e6b34] dark:text-[#8fd0a3]">
            Nota: {{ number_format($activity->grade, 2, ',', '.') }}
        </p>
    @endif

    @if ($activity->feedback)
        <p class="mt-1 text-xs leading-5 text-[#172019] dark:text-white">{{ $activity->feedback }}</p>
    @endif
</section>
