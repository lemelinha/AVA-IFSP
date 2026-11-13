@extends('layouts.app')

@section('title', 'Processos RED — Área CTI')

@section('content')
    <x-page-header
        title="Processos RED"
        subtitle="Todos os processos de Regime de Ensino a Distância do campus. A conclusão e a reativação são da CTI."
    />

    @include('partials.flash')

    <section aria-label="Resumo" class="mt-7 overflow-hidden rounded-2xl border border-[#d9e1d9] bg-white dark:border-white/10 dark:bg-[#102319]">
        <div class="grid grid-cols-2 lg:grid-cols-4">
            <div class="border-r border-b border-[#eef2ee] p-5 last:border-r-0 lg:border-b-0 dark:border-white/10">
                <div class="text-[26px] font-semibold tracking-tight text-[#006437] dark:text-[#73c69b]">{{ $activeCount }}</div>
                <div class="mt-1 text-xs leading-5 text-[#647168] dark:text-white/60">Processos ativos</div>
            </div>
            <div class="border-r border-[#eef2ee] p-5 lg:border-b-0 dark:border-white/10">
                <div class="text-[26px] font-semibold tracking-tight {{ $awaitingValidationCount > 0 ? 'text-[#d12a2e] dark:text-[#f2a3a5]' : 'text-[#006437] dark:text-[#73c69b]' }}">{{ $awaitingValidationCount }}</div>
                <div class="mt-1 text-xs leading-5 text-[#647168] dark:text-white/60">Atividades para validar</div>
            </div>
            <div class="border-r border-[#eef2ee] p-5 lg:border-b-0 dark:border-white/10">
                <div class="text-[26px] font-semibold tracking-tight text-[#006437] dark:text-[#73c69b]">{{ $totalCount }}</div>
                <div class="mt-1 text-xs leading-5 text-[#647168] dark:text-white/60">Processos no total</div>
            </div>
            <div class="p-5">
                <div class="text-[26px] font-semibold tracking-tight text-[#006437] dark:text-[#73c69b]">{{ $activityCount }}</div>
                <div class="mt-1 text-xs leading-5 text-[#647168] dark:text-white/60">Atividades vinculadas</div>
            </div>
        </div>
    </section>

    <x-search-bar
        :action="route('cti.processes.index')"
        placeholder="Buscar por processo, aluno ou prontuário"
        :filters="[
            [
                'name' => 'status',
                'label' => 'Status',
                'placeholder' => 'Todos os status',
                'options' => ['active' => 'Ativo', 'concluded' => 'Concluído'],
            ],
        ]"
        :values="$filters"
    />

    <div class="mt-5">
        <x-data-table
            aria-label="Lista de processos RED"
            :columns="[
                'Processo',
                'Aluno',
                'Coordenador',
                'Atividades',
                'Status',
                'Início',
                ['label' => 'Ações', 'align' => 'right'],
            ]"
            :empty-message="'Nenhum processo RED cadastrado ainda.'"
            :is-empty="$processes->isEmpty()"
            min-width="900px"
        >
            @foreach ($processes as $process)
                <tr class="border-b border-[#eef2ee] transition last:border-b-0 hover:bg-[#f7faf7] dark:border-white/10 dark:hover:bg-white/[0.04]">
                    <td class="px-5 py-4 align-middle text-sm font-semibold text-[#172019] dark:text-white">{{ $process->public_id }}</td>
                    <td class="px-5 py-4 align-middle text-sm">
                        <span class="text-[#172019] dark:text-white">{{ $process->student->name }}</span>
                        <span class="mt-0.5 block text-xs font-normal text-[#647168] dark:text-white/55">Prontuário {{ $process->student->prontuario }}</span>
                    </td>
                    <td class="px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">{{ $process->coordinator->name }}</td>
                    <td class="px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">{{ $process->activities->count() }} vinculadas</td>
                    <td class="px-5 py-4 align-middle">
                        <x-badge :status="$process->status->value" :label="$process->status === \App\Enums\ProcessStatus::Active ? 'Ativo' : 'Concluído'" />
                    </td>
                    <td class="px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">
                        {{ $process->started_at?->format('d/m/Y') ?? $process->created_at->format('d/m/Y') }}
                    </td>
                    <td class="px-5 py-4 text-right align-middle">
                        <span class="inline-flex items-center gap-3 text-xs font-semibold">
                            <x-button variant="link" href="{{ route('cti.processes.show', $process) }}">Detalhes</x-button>

                            @if ($process->status === \App\Enums\ProcessStatus::Active)
                                <x-button
                                    variant="link"
                                    action="{{ route('cti.processes.conclude', $process) }}"
                                    method="PATCH"
                                    confirm="Concluir {{ $process->public_id }}? O aluno volta para as aulas presenciais."
                                >
                                    Concluir
                                </x-button>
                            @else
                                <x-button variant="info" action="{{ route('cti.processes.activate', $process) }}" method="PATCH">Reativar</x-button>
                            @endif

                            <x-button
                                variant="danger"
                                action="{{ route('cti.processes.destroy', $process) }}"
                                method="DELETE"
                                confirm="Excluir {{ $process->public_id }}? As atividades vinculadas também serão excluídas."
                            >
                                Excluir
                            </x-button>
                        </span>
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </div>
@endsection
