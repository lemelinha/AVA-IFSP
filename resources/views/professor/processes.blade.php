@extends('layouts.app')

@section('title', 'Processos RED — Professor')

@section('content')
    <x-page-header
        title="Processos RED"
        subtitle="Processos em que você foi vinculado como professor."
    />

    @include('partials.flash')

    <x-search-bar
        :action="route('professor.processes.index')"
        placeholder="Buscar por processo, aluno ou prontuário"
        :values="$filters"
    />

    <div class="mt-5">
        <x-data-table
            aria-label="Processos do professor"
            :columns="[
                'Processo',
                'Aluno',
                'Suas atividades',
                'Status',
                'Início',
                ['label' => 'Ações', 'align' => 'right'],
            ]"
            :empty-message="'Nenhum processo vinculado a você.'"
            :is-empty="$processes->isEmpty()"
            min-width="820px"
        >
            @foreach ($processes as $process)
                <tr class="border-b border-[#eef2ee] transition last:border-b-0 hover:bg-[#f7faf7] dark:border-white/10 dark:hover:bg-white/[0.04]">
                    <td class="px-5 py-4 align-middle text-sm font-semibold text-[#172019] dark:text-white">{{ $process->public_id }}</td>
                    <td class="px-5 py-4 align-middle text-sm">
                        <span class="text-[#172019] dark:text-white">{{ $process->student->name }}</span>
                        <span class="mt-0.5 block text-xs font-normal text-[#647168] dark:text-white/55">Prontuário {{ $process->student->prontuario }}</span>
                    </td>
                    <td class="px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">{{ $process->activities->count() }} vinculada(s)</td>
                    <td class="px-5 py-4 align-middle">
                        <x-badge :status="$process->status->value" :label="$process->status === \App\Enums\ProcessStatus::Active ? 'Ativo' : 'Concluído'" />
                    </td>
                    <td class="px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">
                        {{ $process->started_at?->format('d/m/Y') ?? $process->created_at->format('d/m/Y') }}
                    </td>
                    <td class="px-5 py-4 text-right align-middle">
                        <span class="inline-flex items-center gap-3 text-xs font-semibold">
                            <x-button variant="link" href="{{ route('professor.processes.show', $process) }}">Abrir</x-button>
                        </span>
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </div>
@endsection