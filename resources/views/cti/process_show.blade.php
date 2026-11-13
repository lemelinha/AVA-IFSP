@extends('layouts.app')

@section('title', $process->public_id . ' — Processo RED (CTI)')

@section('content')
    <x-page-header
        :title="$process->public_id"
        :subtitle="'Aluno: ' . $process->student->name . ' · Prontuário ' . $process->student->prontuario"
        :back="route('cti.processes.index')"
        back-label="Processos"
    >
        <x-slot:actions>
            @if ($process->status === \App\Enums\ProcessStatus::Active)
                <x-button
                    action="{{ route('cti.processes.conclude', $process) }}"
                    method="PATCH"
                    confirm="Concluir {{ $process->public_id }}? O aluno volta para as aulas presenciais."
                >
                    Concluir processo
                </x-button>
            @else
                <x-button variant="info" action="{{ route('cti.processes.activate', $process) }}" method="PATCH">
                    Reativar processo
                </x-button>
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
        <p class="mt-3 text-sm text-[#647168] dark:text-white/60">Nenhuma disciplina vinculada.</p>
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

    @if ($process->activities->isEmpty())
        <p class="mt-3 text-sm text-[#647168] dark:text-white/60">Nenhuma atividade criada neste processo.</p>
    @else
        <div class="mt-4 space-y-4">
            @foreach ($process->activities as $activity)
                @php
                    $isOverdue = $activity->deadline !== null
                        && $activity->deadline->isPast()
                        && ! in_array($activity->status, [\App\Enums\ActivityStatus::Corrected, \App\Enums\ActivityStatus::Concluded], true);
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

                        @include('partials.activity_material', ['activity' => $activity, 'fileRoute' => 'cti.processes.activities.file', 'routeParams' => [$process, $activity]])

                        @if ($activity->url_answer || $activity->answer_text)
                            <div class="mt-4">
                                @include('partials.activity_answer', ['activity' => $activity, 'fileRoute' => 'cti.processes.activities.answer', 'routeParams' => [$process, $activity]])
                            </div>
                        @endif

                        <p class="mt-4 flex flex-wrap gap-x-4 gap-y-1 text-xs text-[#647168] dark:text-white/55">
                            @if ($activity->published_at)
                                <span>Publicada em {{ $activity->published_at->format('d/m/Y H:i') }}</span>
                            @endif
                            @if ($activity->answered_at)
                                <span>Respondida em {{ $activity->answered_at->format('d/m/Y H:i') }}</span>
                            @endif
                            @if ($activity->corrected_at)
                                <span>Corrigida em {{ $activity->corrected_at->format('d/m/Y H:i') }}</span>
                            @endif
                            @if ($activity->concluded_at)
                                <span>Validada em {{ $activity->concluded_at->format('d/m/Y H:i') }}</span>
                            @endif
                        </p>
                    </div>
                </section>
            @endforeach
        </div>
    @endif
@endsection
