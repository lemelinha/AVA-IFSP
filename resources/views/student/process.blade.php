@extends('layouts.app')

@section('title', 'Meu processo RED — Aluno')

@section('content')
    <x-page-header
        :title="$process->public_id"
        :subtitle="'Coordenador: ' . $process->coordinator->name"
    />

    @include('partials.flash')

    <section aria-label="Dados do processo" class="mt-7 overflow-hidden rounded-2xl border border-[#d9e1d9] bg-white dark:border-white/10 dark:bg-[#102319]">
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

    <h2 class="mt-8 text-lg font-semibold tracking-tight text-[#172019] dark:text-white">Atividades</h2>

    @if ($process->activities->isEmpty())
        <p class="mt-3 text-sm text-[#647168] dark:text-white/60">Nenhuma atividade publicada ainda.</p>
    @else
        <div class="mt-4 space-y-4">
            @foreach ($process->activities as $activity)
                <section class="overflow-hidden rounded-2xl border border-[#d9e1d9] bg-white dark:border-white/10 dark:bg-[#102319]">
                    <header class="flex flex-col gap-1 border-b border-[#eef2ee] px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
                        <div>
                            <h3 class="text-sm font-semibold text-[#172019] dark:text-white">{{ $activity->title }}</h3>
                            <p class="mt-0.5 text-xs text-[#647168] dark:text-white/55">
                                {{ $activity->discipline->label }} · Professor: {{ $activity->professor->name }} · Prazo: {{ $activity->deadline?->format('d/m/Y') ?? '—' }}
                            </p>
                        </div>
                        <x-badge :status="$activity->status->value" :label="$activity->status->label()" />
                    </header>

                    <div class="p-5">
                        <p class="text-sm text-[#172019] dark:text-white">{{ $activity->description }}</p>

                        @if ($activity->url_activity)
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($activity->url_activity) }}" target="_blank" class="mt-3 inline-flex text-xs font-semibold text-[#006437] hover:underline dark:text-[#73c69b]">
                                Baixar atividade (PDF) →
                            </a>
                        @endif

                        @if ($activity->status === \App\Enums\ActivityStatus::Sent)
                            <div class="mt-4">
                                <a href="{{ route('aluno.activity.show', $activity) }}" class="inline-flex h-10 items-center rounded-lg bg-[#006437] px-4 text-xs font-semibold text-white transition hover:bg-[#004d2a] dark:bg-[#0f7a48] dark:hover:bg-[#0c6a3f]">
                                    Enviar resolução
                                </a>
                            </div>
                        @endif

                        @if ($activity->url_answer)
                            <div class="mt-4 rounded-xl border border-[#d9e1d9] bg-[#f7faf7] p-4 dark:border-white/10 dark:bg-white/[0.03]">
                                <p class="text-xs font-semibold text-[#172019] dark:text-white">Sua resolução</p>
                                @if ($activity->answer_type === 'pdf')
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($activity->url_answer) }}" target="_blank" class="mt-1 inline-flex text-xs font-semibold text-[#006437] hover:underline dark:text-[#73c69b]">Abrir PDF →</a>
                                @else
                                    <a href="{{ $activity->url_answer }}" target="_blank" class="mt-1 inline-flex text-xs font-semibold text-[#006437] hover:underline dark:text-[#73c69b]">{{ $activity->url_answer }}</a>
                                @endif

                                @if ($activity->grade !== null)
                                    <p class="mt-3 text-xs font-semibold text-[#1e6b34] dark:text-[#8fd0a3]">Nota: {{ number_format($activity->grade, 2, ',', '.') }}</p>
                                @endif
                                @if ($activity->feedback)
                                    <p class="mt-1 text-xs text-[#172019] dark:text-white">{{ $activity->feedback }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </section>
            @endforeach
        </div>
    @endif
@endsection