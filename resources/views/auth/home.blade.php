@extends('layouts.app')

@section('title', 'Painel — Sistema RED')

@php
    $titles = [
        'cti' => 'Painel do campus',
        'coordinator' => 'Seus processos RED',
        'professor' => 'Suas atividades RED',
        'none' => 'Painel',
    ];

    $subtitles = [
        'cti' => 'Visão geral de processos, usuários e cursos do campus.',
        'coordinator' => 'Acompanhe os processos e valide as atividades dos alunos.',
        'professor' => 'Publique, acompanhe e corrija as atividades dos seus alunos.',
        'none' => 'Sua conta ainda não possui um perfil de acesso.',
    ];

    $processesRoute = match ($view) {
        'cti' => route('cti.processes.index'),
        'coordinator' => route('coordinator.processes.index'),
        'professor' => route('professor.processes.index'),
        default => null,
    };

    $emptyMessages = [
        'cti' => 'Nenhum processo RED cadastrado no campus.',
        'coordinator' => 'Nenhum processo RED aberto por você. Comece abrindo um novo processo.',
        'professor' => 'Nenhum processo vinculado a você. A coordenação avisará quando houver um aluno em RED.',
    ];

    $activityCounts = [
        'cti' => fn ($process) => $process->activities_count,
        'coordinator' => fn ($process) => $process->activities->count(),
        'professor' => fn ($process) => $process->own_activities_count,
    ];
@endphp

@section('content')
    <x-page-header :title="$titles[$view]" :subtitle="$subtitles[$view]">
        <x-slot:actions>
            @if ($view === 'cti')
                <x-button href="{{ route('cti.processes.index') }}">Gerenciar processos</x-button>
                <x-button variant="secondary" href="{{ route('cti.users.create') }}">Novo usuário</x-button>
            @elseif ($view === 'coordinator')
                <x-button href="{{ route('coordinator.processes.create') }}">Novo processo</x-button>
                <x-button variant="secondary" href="{{ route('coordinator.students.index') }}">Alunos</x-button>
            @elseif ($view === 'professor')
                <x-button href="{{ route('professor.processes.index') }}">Meus processos</x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    @include('partials.flash')

    @if ($view === 'none')
        <div class="mt-7 flex flex-col items-center gap-4 rounded-2xl border border-dashed border-[#d9e1d9] bg-white px-6 py-14 text-center dark:border-white/10 dark:bg-[#102319]">
            <svg class="h-10 w-10 text-[#647168] dark:text-white/45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm7.5-3a7.5 7.5 0 0 1-.12 1.36l2.06 1.6-2 3.46-2.4-.96a7.5 7.5 0 0 1-2.35 1.36L14.25 22h-4.5l-.44-2.18a7.5 7.5 0 0 1-2.35-1.36l-2.4.96-2-3.46 2.06-1.6a7.5 7.5 0 0 1 0-2.72l-2.06-1.6 2-3.46 2.4.96A7.5 7.5 0 0 1 9.31 2.18L9.75 0h4.5l.44 2.18a7.5 7.5 0 0 1 2.35 1.36l2.4-.96 2 3.46-2.06 1.6c.08.44.12.9.12 1.36Z" />
            </svg>
            <div>
                <p class="text-sm font-semibold text-[#172019] dark:text-white">Sem perfil de acesso</p>
                <p class="mt-1 max-w-md text-sm leading-6 text-[#647168] dark:text-white/60">
                    Sua conta ainda não foi vinculada a um perfil. Procure a CTI do campus para liberar o acesso.
                </p>
            </div>
        </div>
    @else
        <section aria-label="Resumo do painel" class="mt-7 overflow-hidden rounded-2xl border border-[#d9e1d9] bg-white dark:border-white/10 dark:bg-[#102319]">
            <div class="grid grid-cols-2 lg:grid-cols-4">
                @foreach ($counters as $counter)
                    <div class="border-r border-b border-[#eef2ee] p-5 [&:nth-child(2n)]:border-r-0 [&:nth-child(n+3)]:border-b-0 lg:border-r lg:border-b-0 lg:[&:nth-child(2n)]:border-r lg:last:border-r-0 dark:border-white/10">
                        <div class="text-[26px] font-semibold tracking-tight {{ $counter['warn'] ? 'text-[#d12a2e] dark:text-[#f2a3a5]' : 'text-[#006437] dark:text-[#73c69b]' }}">
                            {{ $counter['n'] }}
                        </div>
                        <div class="mt-1 text-xs leading-5 text-[#647168] dark:text-white/60">{{ $counter['l'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="mt-8 flex items-end justify-between gap-4">
            <h2 class="text-lg font-semibold tracking-tight text-[#172019] dark:text-white">Processos recentes</h2>

            @if ($processesRoute)
                <a href="{{ $processesRoute }}" class="text-xs font-semibold text-[#006437] transition hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#006437]/40 dark:text-[#73c69b]">
                    Ver todos →
                </a>
            @endif
        </div>

        <div class="mt-3">
            <x-data-table
                aria-label="Processos RED recentes"
                :columns="[
                    'Processo',
                    'Aluno',
                    'Disciplinas',
                    'Atividades',
                    'Status',
                    'Início',
                    ['label' => 'Ações', 'align' => 'right'],
                ]"
                :empty-message="$emptyMessages[$view]"
                :is-empty="$processes->isEmpty()"
                min-width="920px"
            >
                @foreach ($processes as $process)
                    <tr class="border-b border-[#eef2ee] transition last:border-b-0 hover:bg-[#f7faf7] dark:border-white/10 dark:hover:bg-white/[0.04]">
                        <td class="px-5 py-4 align-middle text-sm font-semibold text-[#172019] dark:text-white">{{ $process->public_id }}</td>
                        <td class="px-5 py-4 align-middle text-sm">
                            <span class="text-[#172019] dark:text-white">{{ $process->student->name }}</span>
                            <span class="mt-0.5 block text-xs font-normal text-[#647168] dark:text-white/55">Prontuário {{ $process->student->prontuario }}</span>
                        </td>
                        <td class="max-w-[240px] px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">
                            <span>{{ $process->disciplines->count() }} vinculada(s)</span>
                            @if ($process->disciplines->isNotEmpty())
                                <span class="mt-0.5 block truncate text-xs text-[#647168] dark:text-white/50">{{ $process->disciplines->pluck('label')->implode(', ') }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">{{ $activityCounts[$view]($process) }}</td>
                        <td class="px-5 py-4 align-middle">
                            <x-badge :status="$process->status->value" :label="$process->status === \App\Enums\ProcessStatus::Active ? 'Ativo' : 'Concluído'" />
                        </td>
                        <td class="px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">
                            {{ $process->started_at?->format('d/m/Y') ?? $process->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-4 text-right align-middle">
                            @if ($view === 'cti')
                                <x-button variant="link" href="{{ route('cti.processes.index') }}">Gerenciar</x-button>
                            @elseif ($view === 'coordinator')
                                <x-button variant="link" href="{{ route('coordinator.processes.show', $process) }}">Abrir</x-button>
                            @elseif ($view === 'professor')
                                <x-button variant="link" href="{{ route('professor.processes.show', $process) }}">Abrir</x-button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>

        @if ($view === 'coordinator')
            <x-hint>Abra um novo processo para vincular um aluno às disciplinas e avisar os professores por e-mail.</x-hint>
        @elseif ($view === 'professor')
            <x-hint>Publique as atividades pendentes para que o aluno possa enviar a resolução.</x-hint>
        @elseif ($view === 'cti')
            <x-hint>A área CTI concentra usuários, cursos, disciplinas e o acompanhamento de todos os processos.</x-hint>
        @endif
    @endif
@endsection
