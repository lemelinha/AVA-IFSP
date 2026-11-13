@extends('layouts.app')

@section('title', 'Meu processo RED — Aluno')

@section('content')
    @if (! $process)
        <x-page-header title="Meu processo RED" subtitle="Acompanhe as atividades do seu Regime de Ensino a Distância." />

        <div class="mt-7 flex flex-col items-center gap-4 rounded-2xl border border-dashed border-[#d9e1d9] bg-white px-6 py-14 text-center dark:border-white/10 dark:bg-[#102319]">
            <svg class="h-10 w-10 text-[#647168] dark:text-white/45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.25v11.5m0-11.5C10.83 5.5 9.5 5 8 5c-1.5 0-2.5.5-3 .5v12c.5 0 1.5-.5 3-.5s2.5.5 3 .5m0-12c1.17 0 2.5-.5 4-.5 1.5 0 2.5.5 3 .5v12c-.5 0-1.5-.5-3-.5s-2.5.5-3 .5m0-12v12" />
            </svg>
            <div>
                <p class="text-sm font-semibold text-[#172019] dark:text-white">Você ainda não possui um processo RED</p>
                <p class="mt-1 text-sm leading-6 text-[#647168] dark:text-white/60">
                    Procure a coordenação do seu curso para abrir a sua solicitação de Regime de Ensino a Distância.
                </p>
            </div>
        </div>
    @else
        @php
            $activities = $process->activities;
            $counts = [
                'waiting' => $activities->where('status', \App\Enums\ActivityStatus::Sent)->count(),
                'in_correction' => $activities->where('status', \App\Enums\ActivityStatus::InCorrection)->count(),
                'done' => $activities->whereIn('status', [\App\Enums\ActivityStatus::Corrected, \App\Enums\ActivityStatus::Concluded])->count(),
                'total' => $activities->count(),
            ];
        @endphp

        <x-page-header
            :title="$process->public_id"
            :subtitle="'Coordenador: ' . $process->coordinator->name"
        />

        @include('partials.flash')

        @if ($process->status === \App\Enums\ProcessStatus::Concluded)
            <div class="mt-5 flex items-start gap-2 rounded-xl border border-[#f0dcb0] bg-[#fdf3e0] px-4 py-3 text-sm leading-5 text-[#8a5a00] dark:border-[#e8c07a]/30 dark:bg-[#fdf3e0]/10 dark:text-[#e8c07a]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86l-8.02 13.9A2 2 0 0 0 3.98 21h16.04a2 2 0 0 0 1.71-3.24L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                </svg>
                <span>Processo finalizado em {{ $process->concluded_at?->format('d/m/Y') }}. As atividades não aceitam mais respostas.</span>
            </div>
        @endif

        <section aria-label="Resumo das atividades" class="mt-7 overflow-hidden rounded-2xl border border-[#d9e1d9] bg-white dark:border-white/10 dark:bg-[#102319]">
            <div class="grid grid-cols-2 lg:grid-cols-4">
                <div class="border-r border-b border-[#eef2ee] p-5 last:border-r-0 lg:border-b-0 dark:border-white/10">
                    <div class="text-[26px] font-semibold tracking-tight text-[#006437] dark:text-[#73c69b]">{{ $counts['total'] }}</div>
                    <div class="mt-1 text-xs leading-5 text-[#647168] dark:text-white/60">Atividades no processo</div>
                </div>
                <div class="border-r border-[#eef2ee] p-5 lg:border-b-0 dark:border-white/10">
                    <div class="text-[26px] font-semibold tracking-tight {{ $counts['waiting'] > 0 ? 'text-[#d12a2e] dark:text-[#f2a3a5]' : 'text-[#006437] dark:text-[#73c69b]' }}">{{ $counts['waiting'] }}</div>
                    <div class="mt-1 text-xs leading-5 text-[#647168] dark:text-white/60">Aguardando sua resposta</div>
                </div>
                <div class="border-r border-[#eef2ee] p-5 lg:border-b-0 dark:border-white/10">
                    <div class="text-[26px] font-semibold tracking-tight text-[#006437] dark:text-[#73c69b]">{{ $counts['in_correction'] }}</div>
                    <div class="mt-1 text-xs leading-5 text-[#647168] dark:text-white/60">Aguardando correção</div>
                </div>
                <div class="p-5">
                    <div class="text-[26px] font-semibold tracking-tight text-[#006437] dark:text-[#73c69b]">{{ $counts['done'] }}</div>
                    <div class="mt-1 text-xs leading-5 text-[#647168] dark:text-white/60">Corrigidas ou concluídas</div>
                </div>
            </div>
        </section>

        <section aria-label="Dados do processo" class="mt-5 overflow-hidden rounded-2xl border border-[#d9e1d9] bg-white dark:border-white/10 dark:bg-[#102319]">
            <dl class="grid gap-6 p-6 sm:grid-cols-2 sm:p-8">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-[#647168] dark:text-white/45">Status</dt>
                    <dd class="mt-2">
                        <x-badge :status="$process->status->value" :label="$process->status === \App\Enums\ProcessStatus::Active ? 'Ativo' : 'Concluído'" />
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-[#647168] dark:text-white/45">Início</dt>
                    <dd class="mt-2 text-sm leading-6 text-[#172019] dark:text-white">{{ $process->started_at?->format('d/m/Y') ?? $process->created_at->format('d/m/Y') }}</dd>
                </div>
            </dl>
        </section>

        <h2 class="mt-8 text-lg font-semibold tracking-tight text-[#172019] dark:text-white">Minhas disciplinas</h2>

        @if ($process->disciplines->isEmpty())
            <p class="mt-3 text-sm text-[#647168] dark:text-white/60">Nenhuma disciplina vinculada ao seu processo.</p>
        @else
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                @foreach ($process->disciplines as $discipline)
                    @php
                        $professor = $discipline->professors->firstWhere('id', $discipline->pivot->professor_id);
                    @endphp

                    <div class="rounded-xl border border-[#d9e1d9] bg-white p-4 dark:border-white/10 dark:bg-[#102319]">
                        <p class="text-sm font-semibold text-[#172019] dark:text-white">{{ $discipline->label }}</p>
                        <p class="mt-1 text-xs leading-5 text-[#647168] dark:text-white/60">
                            {{ $discipline->class_code }} · Professor: {{ $professor?->name ?? 'a definir' }}
                        </p>
                    </div>
                @endforeach
            </div>
        @endif

        <h2 class="mt-8 text-lg font-semibold tracking-tight text-[#172019] dark:text-white">Atividades</h2>

        @if ($activities->isEmpty())
            <p class="mt-3 text-sm text-[#647168] dark:text-white/60">Nenhuma atividade publicada ainda. Volte em breve.</p>
        @else
            <div class="mt-4 space-y-4">
                @foreach ($activities as $activity)
                    @php
                        $isOverdue = $activity->deadline !== null
                            && $activity->deadline->isPast()
                            && ! in_array($activity->status, [\App\Enums\ActivityStatus::Corrected, \App\Enums\ActivityStatus::Concluded], true);
                        $canAnswer = $process->status === \App\Enums\ProcessStatus::Active
                            && in_array($activity->status, [\App\Enums\ActivityStatus::Sent, \App\Enums\ActivityStatus::InCorrection], true);
                    @endphp

                    <section class="overflow-hidden rounded-2xl border border-[#d9e1d9] bg-white dark:border-white/10 dark:bg-[#102319]">
                        <header class="flex flex-col gap-1 border-b border-[#eef2ee] px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
                            <div>
                                <h3 class="text-sm font-semibold text-[#172019] dark:text-white">{{ $activity->title }}</h3>
                                <p class="mt-0.5 text-xs text-[#647168] dark:text-white/55">
                                    {{ $activity->discipline->label }} · Professor: {{ $activity->professor->name }} · Prazo: {{ $activity->deadline?->format('d/m/Y') ?? '—' }}
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($isOverdue)
                                    <x-badge status="overdue" />
                                @endif
                                <x-badge :status="$activity->status->value" />
                            </div>
                        </header>

                        <div class="p-5">
                            <p class="text-sm text-[#172019] dark:text-white">{{ $activity->description }}</p>

                            @include('partials.activity_material', ['activity' => $activity])

                            @if ($activity->url_answer || $activity->answer_text)
                                <div class="mt-4">
                                    @include('partials.activity_answer', ['activity' => $activity, 'heading' => 'Sua resolução'])
                                </div>
                            @endif

                            <div class="mt-4">
                                @if ($canAnswer)
                                    <a href="{{ route('aluno.activity.show', $activity) }}" class="inline-flex h-10 items-center rounded-lg bg-[#006437] px-4 text-xs font-semibold text-white transition hover:bg-[#004d2a] dark:bg-[#0f7a48] dark:hover:bg-[#0c6a3f]">
                                        {{ $activity->url_answer || $activity->answer_text ? 'Reenviar resolução' : 'Enviar resolução' }}
                                    </a>
                                @elseif (in_array($activity->status, [\App\Enums\ActivityStatus::Corrected, \App\Enums\ActivityStatus::Concluded], true))
                                    <a href="{{ route('aluno.activity.show', $activity) }}" class="inline-flex h-10 items-center rounded-lg border border-[#006437]/30 px-4 text-xs font-semibold text-[#006437] transition hover:bg-[#006437]/5 dark:border-[#73c69b]/30 dark:text-[#73c69b] dark:hover:bg-[#73c69b]/10">
                                        Ver resultado
                                    </a>
                                @elseif ($activity->status === \App\Enums\ActivityStatus::Pending)
                                    <span class="text-xs text-[#647168] dark:text-white/55">Aguardando publicação pelo professor.</span>
                                @endif
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    @endif
@endsection
