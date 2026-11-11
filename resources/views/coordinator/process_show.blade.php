@extends('layouts.app')

@section('title', $process->public_id . ' — Processo RED')

@section('content')
    <x-page-header
        :title="$process->public_id"
        :subtitle="'Aluno: ' . $process->student->name . ' · Prontuário ' . $process->student->prontuario"
        :back="route('coordinator.processes.index')"
        back-label="Processos"
    >
        <x-slot:actions>
            @if ($process->status === \App\Enums\ProcessStatus::Active)
                @if ($process->activities->isNotEmpty() && $process->activities->every(fn ($activity) => $activity->status === \App\Enums\ActivityStatus::Concluded))
                    <x-button
                        variant="primary"
                        action="{{ route('coordinator.processes.conclude', $process) }}"
                        method="PATCH"
                        confirm="Concluir {{ $process->public_id }}? O aluno volta para as aulas presenciais."
                    >
                        Finalizar processo
                    </x-button>
                @endif
            @else
                <x-badge status="concluded" label="Concluído" />
            @endif
        </x-slot:actions>
    </x-page-header>

    @include('partials.flash')

    <section aria-label="Dados do processo" class="mt-7 overflow-hidden rounded-2xl border border-[#d9e1d9] bg-white dark:border-white/10 dark:bg-[#102319]">
        <dl class="grid gap-6 p-6 sm:grid-cols-2 sm:p-8">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-[#647168] dark:text-white/45">Aluno</dt>
                <dd class="mt-2 text-sm leading-6 text-[#172019] dark:text-white">{{ $process->student->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-[#647168] dark:text-white/45">Coordenador</dt>
                <dd class="mt-2 text-sm leading-6 text-[#172019] dark:text-white">{{ $process->coordinator->name }}</dd>
            </div>
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
            @if ($process->concluded_at)
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-[#647168] dark:text-white/45">Finalização</dt>
                    <dd class="mt-2 text-sm leading-6 text-[#172019] dark:text-white">{{ $process->concluded_at->format('d/m/Y') }}</dd>
                </div>
            @endif
        </dl>
    </section>

    <h2 class="mt-8 text-lg font-semibold tracking-tight text-[#172019] dark:text-white">Disciplinas vinculadas</h2>

    @if ($process->disciplines->isEmpty())
        <p class="mt-3 text-sm text-[#647168] dark:text-white/60">Nenhuma disciplina vinculada a este processo.</p>
    @else
        <div class="mt-4 space-y-5">
            @foreach ($process->disciplines as $discipline)
                @php
                    $activities = $process->activities->where('discipline_id', $discipline->id)->values();
                    $professor = $discipline->professors->firstWhere('id', $discipline->pivot->professor_id);
                @endphp
                <section class="overflow-hidden rounded-2xl border border-[#d9e1d9] bg-white dark:border-white/10 dark:bg-[#102319]">
                    <header class="flex flex-col gap-1 border-b border-[#eef2ee] px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
                        <div>
                            <h3 class="text-sm font-semibold text-[#172019] dark:text-white">{{ $discipline->label }}</h3>
                            <p class="mt-0.5 text-xs text-[#647168] dark:text-white/55">
                                {{ $discipline->course->label }} · Professor: {{ $professor?->name ?? 'não definido' }}
                            </p>
                        </div>
                        <span class="text-xs font-semibold text-[#647168] dark:text-white/55">{{ $activities->count() }} atividade(s)</span>
                    </header>

                    @if ($activities->isEmpty())
                        <p class="px-5 py-6 text-sm text-[#647168] dark:text-white/60">Aguardando o professor publicar as atividades.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[640px] border-collapse text-left">
                                <thead>
                                    <tr class="border-b border-[#eef2ee] bg-[#f7faf7] dark:border-white/10 dark:bg-white/[0.03]">
                                        <th scope="col" class="px-5 py-3 text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Atividade</th>
                                        <th scope="col" class="px-5 py-3 text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Status</th>
                                        <th scope="col" class="px-5 py-3 text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Prazo</th>
                                        <th scope="col" class="px-5 py-3 text-right text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($activities as $activity)
                                        <tr class="border-b border-[#eef2ee] transition last:border-b-0 hover:bg-[#f7faf7] dark:border-white/10 dark:hover:bg-white/[0.04]">
                                            <td class="px-5 py-4 align-middle text-sm">
                                                <span class="font-medium text-[#172019] dark:text-white">{{ $activity->title ?: 'Atividade sem título' }}</span>
                                                @if ($activity->grade !== null)
                                                    <span class="mt-0.5 block text-xs font-normal text-[#647168] dark:text-white/55">Nota: {{ number_format($activity->grade, 2, ',', '.') }}</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 align-middle">
                                                <x-badge :status="$activity->status->value" :label="$activity->status->label()" />
                                            </td>
                                            <td class="px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">
                                                {{ $activity->deadline?->format('d/m/Y') ?? '—' }}
                                            </td>
                                            <td class="px-5 py-4 text-right align-middle">
                                                <span class="inline-flex items-center gap-3 text-xs font-semibold">
                                                    @if ($activity->status === \App\Enums\ActivityStatus::Corrected)
                                                        <x-button
                                                            variant="link"
                                                            action="{{ route('coordinator.processes.activities.validate', [$process, $activity]) }}"
                                                            method="PATCH"
                                                            confirm="Validar a atividade {{ $activity->title ?: '' }}?"
                                                        >
                                                            Validar
                                                        </x-button>
                                                    @elseif ($activity->status === \App\Enums\ActivityStatus::Concluded)
                                                        <span class="text-[#1e6b34] dark:text-[#8fd0a3]">Validada</span>
                                                    @else
                                                        <span class="text-[#647168] dark:text-white/55">Aguardando</span>
                                                    @endif
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @endforeach
        </div>
    @endif

    @if ($process->activities->isNotEmpty())
        <p class="mt-4 flex items-start gap-2 text-xs leading-5 text-[#647168] dark:text-white/60">
            <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <circle cx="12" cy="12" r="9" />
                <path stroke-linecap="round" d="M12 11v5m0-8.5v.5" />
            </svg>
            <span>O processo só pode ser finalizado quando todas as atividades estiverem validadas.</span>
        </p>
    @endif
@endsection
