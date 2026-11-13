@props(['activity', 'fileRoute' => 'aluno.activity.file', 'routeParams' => null])

<div class="mt-3">
    @if ($activity->activity_type === 'pdf' && $activity->url_activity)
        <a href="{{ route($fileRoute, $routeParams ?? $activity) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#006437] transition hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#006437]/40 dark:text-[#73c69b]">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0 4-4m-4 4-4-4M4 20h16" />
            </svg>
            Baixar enunciado (PDF) →
        </a>
    @elseif ($activity->activity_type === 'link' && $activity->url_activity)
        <a href="{{ $activity->url_activity }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#006437] transition hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#006437]/40 dark:text-[#73c69b]">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14 4h6v6M20 4l-9 9M9 5H5a1 1 0 0 0-1 1v13a1 1 0 0 0 1 1h13a1 1 0 0 0 1-1v-4" />
            </svg>
            Abrir enunciado (link) →
        </a>
    @elseif ($activity->activity_type === 'text' && $activity->activity_text)
        <div class="rounded-xl border border-[#d9e1d9] bg-[#f7faf7] p-4 dark:border-white/10 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold text-[#172019] dark:text-white">Enunciado</p>
            <p class="mt-1.5 text-sm leading-6 whitespace-pre-line text-[#172019] dark:text-white">{{ $activity->activity_text }}</p>
        </div>
    @endif
</div>
