@extends('layouts.app')

@section('title', 'Processos RED — Área CTI')

@section('content')
    <div>
        <h1 class="text-[28px] font-semibold tracking-tight text-[#172019] sm:text-3xl dark:text-white">Processos RED</h1>
        <p class="mt-1.5 text-sm leading-6 text-[#647168] dark:text-white/65">Todos os processos de Regime de Ensino a Distância do campus. A conclusão e a reativação são da CTI.</p>
    </div>

    @include('partials.flash')

    <section aria-label="Resumo" class="mt-7 overflow-hidden rounded-2xl border border-[#d9e1d9] bg-white dark:border-white/10 dark:bg-[#102319]">
        <div class="grid grid-cols-2 lg:grid-cols-4">
            <div class="border-r border-b border-[#eef2ee] p-5 last:border-r-0 lg:border-b-0 dark:border-white/10">
                <div class="text-[26px] font-semibold tracking-tight text-[#006437] dark:text-[#73c69b]">{{ $activeCount }}</div>
                <div class="mt-1 text-xs leading-5 text-[#647168] dark:text-white/60">Processos ativos</div>
            </div>
            <div class="border-r border-[#eef2ee] p-5 lg:border-b-0 dark:border-white/10">
                <div class="text-[26px] font-semibold tracking-tight {{ $readyCount > 0 ? 'text-[#d12a2e] dark:text-[#f2a3a5]' : 'text-[#006437] dark:text-[#73c69b]' }}">{{ $readyCount }}</div>
                <div class="mt-1 text-xs leading-5 text-[#647168] dark:text-white/60">Prontos para finalizar</div>
            </div>
            <div class="border-r border-[#eef2ee] p-5 lg:border-b-0 dark:border-white/10">
                <div class="text-[26px] font-semibold tracking-tight text-[#006437] dark:text-[#73c69b]">{{ $processes->count() }}</div>
                <div class="mt-1 text-xs leading-5 text-[#647168] dark:text-white/60">Processos no total</div>
            </div>
            <div class="p-5">
                <div class="text-[26px] font-semibold tracking-tight text-[#006437] dark:text-[#73c69b]">{{ $processes->sum(fn ($process) => $process->activities->count()) }}</div>
                <div class="mt-1 text-xs leading-5 text-[#647168] dark:text-white/60">Atividades vinculadas</div>
            </div>
        </div>
    </section>

    <section aria-label="Lista de processos" class="mt-5 overflow-hidden rounded-2xl border border-[#d9e1d9] bg-white dark:border-white/10 dark:bg-[#102319]">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] border-collapse text-left">
                <caption class="sr-only">Processos RED</caption>
                <thead>
                    <tr class="border-b border-[#eef2ee] bg-[#f7faf7] dark:border-white/10 dark:bg-white/[0.03]">
                        <th scope="col" class="px-5 py-3 text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Processo</th>
                        <th scope="col" class="px-5 py-3 text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Aluno</th>
                        <th scope="col" class="px-5 py-3 text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Coordenador</th>
                        <th scope="col" class="px-5 py-3 text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Atividades</th>
                        <th scope="col" class="px-5 py-3 text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Status</th>
                        <th scope="col" class="px-5 py-3 text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Início</th>
                        <th scope="col" class="px-5 py-3 text-right text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($processes as $process)
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
                                    @if ($process->status === \App\Enums\ProcessStatus::Active)
                                        <form method="POST" action="{{ route('cti.processes.conclude', $process) }}" onsubmit="return confirm('Concluir {{ $process->public_id }}? O aluno volta para as aulas presenciais.')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-[#006437] transition hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#006437]/40 dark:text-[#73c69b]">Concluir</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('cti.processes.activate', $process) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-[#1d4f91] transition hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1d4f91]/40 dark:text-[#9ec5f0]">Reativar</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('cti.processes.destroy', $process) }}" onsubmit="return confirm('Excluir {{ $process->public_id }}? As atividades vinculadas também serão excluídas.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[#a4262c] transition hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#a4262c]/40 dark:text-[#f2a3a5]">Excluir</button>
                                    </form>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-[#647168] dark:text-white/55">Nenhum processo RED cadastrado ainda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
