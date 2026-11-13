@extends('layouts.app')

@section('title', $process->public_id . ' — Processo RED')

@section('content')
    <x-page-header
        :title="$process->public_id"
        :subtitle="'Aluno: ' . $process->student->name . ' · Prontuário ' . $process->student->prontuario"
        :back="route('professor.processes.index')"
        back-label="Processos"
    >
        <x-slot:actions>
            @if ($process->status === \App\Enums\ProcessStatus::Active)
                <x-button href="{{ route('professor.activities.create', ['process' => $process->id]) }}">
                    Nova atividade
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
                <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-[#647168] dark:text-white/45">Status</dt>
                <dd class="mt-2">
                    <x-badge :status="$process->status->value" :label="$process->status === \App\Enums\ProcessStatus::Active ? 'Ativo' : 'Concluído'" />
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-[#647168] dark:text-white/45">Suas disciplinas</dt>
                <dd class="mt-2 text-sm leading-6 text-[#172019] dark:text-white">
                    {{ $disciplines->pluck('label')->implode(', ') ?: '—' }}
                </dd>
            </div>
        </dl>
    </section>

    <h2 class="mt-8 text-lg font-semibold tracking-tight text-[#172019] dark:text-white">Suas atividades</h2>

    @if ($process->activities->isEmpty())
        <p class="mt-3 text-sm text-[#647168] dark:text-white/60">Nenhuma atividade criada ainda. Crie uma atividade para o aluno.</p>
    @else
        <div class="mt-4 space-y-5">
            @foreach ($process->activities as $activity)
                <section class="overflow-hidden rounded-2xl border border-[#d9e1d9] bg-white dark:border-white/10 dark:bg-[#102319]">
                    <header class="flex flex-col gap-1 border-b border-[#eef2ee] px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
                        <div>
                            <h3 class="text-sm font-semibold text-[#172019] dark:text-white">{{ $activity->title ?: 'Atividade sem título' }}</h3>
                            <p class="mt-0.5 text-xs text-[#647168] dark:text-white/55">
                                {{ $activity->discipline->label }} · Prazo: {{ $activity->deadline?->format('d/m/Y') ?? '—' }}
                            </p>
                        </div>
                        <x-badge :status="$activity->status->value" />
                    </header>

                    <div class="p-5">
                        <p class="text-sm text-[#172019] dark:text-white">{{ $activity->description }}</p>

                        @include('partials.activity_material', ['activity' => $activity, 'fileRoute' => 'professor.activities.file'])

                        @if ($activity->url_answer || $activity->answer_text)
                            <div class="mt-4">
                                @include('partials.activity_answer', ['activity' => $activity, 'fileRoute' => 'professor.activities.answer'])
                            </div>
                        @endif

                        @if ($activity->status === \App\Enums\ActivityStatus::InCorrection)
                            <form method="POST" action="{{ route('professor.activities.correct', $activity) }}" class="mt-4 space-y-3 rounded-xl border border-[#d9e1d9] bg-[#f7faf7] p-4 dark:border-white/10 dark:bg-white/[0.03]">
                                @csrf
                                @method('PATCH')
                                <p class="text-xs font-semibold text-[#172019] dark:text-white">Corrigir atividade</p>
                                <div>
                                    <label class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Nota (0 a 10)</label>
                                    <input type="number" name="grade" step="0.01" min="0" max="10" required value="{{ old('grade') }}" class="mt-2 h-11 w-full rounded-lg border border-[#d9e1d9] bg-white px-3.5 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Feedback ao aluno</label>
                                    <textarea name="feedback" rows="3" required class="mt-2 w-full rounded-lg border border-[#d9e1d9] bg-white px-3.5 py-2.5 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10">{{ old('feedback') }}</textarea>
                                </div>
                                <div class="flex justify-end">
                                    <x-button type="submit">Corrigir atividade</x-button>
                                </div>
                            </form>
                        @endif
                    </div>

                    <footer class="flex items-center justify-end gap-3 border-t border-[#eef2ee] px-5 py-3 dark:border-white/10">
                        @if ($activity->status === \App\Enums\ActivityStatus::Pending)
                            <x-button variant="link" action="{{ route('professor.activities.publish', $activity) }}" method="PATCH">Publicar</x-button>
                        @endif
                        @if (in_array($activity->status, [\App\Enums\ActivityStatus::Pending, \App\Enums\ActivityStatus::Sent], true))
                            <x-button variant="link" href="{{ route('professor.activities.edit', $activity) }}">Editar</x-button>
                        @endif
                        @if ($activity->status === \App\Enums\ActivityStatus::Pending || $activity->status === \App\Enums\ActivityStatus::Sent)
                            <x-button variant="danger" action="{{ route('professor.activities.destroy', $activity) }}" method="DELETE" confirm="Excluir a atividade {{ $activity->title }}?">Excluir</x-button>
                        @endif
                    </footer>
                </section>
            @endforeach
        </div>
    @endif
@endsection