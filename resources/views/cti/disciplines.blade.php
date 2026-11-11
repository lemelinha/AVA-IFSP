@extends('layouts.app')

@section('title', 'Disciplinas — Área CTI')

@section('content')
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-[28px] font-semibold tracking-tight text-[#172019] sm:text-3xl dark:text-white">Disciplinas</h1>
            <p class="mt-1.5 text-sm leading-6 text-[#647168] dark:text-white/65">Cada disciplina deve pertencer a um curso e ficar pronta para ser vinculada aos processos RED pelos coordenadores.</p>
        </div>

        <a href="{{ route('cti.disciplines.create') }}" class="h-11 inline-flex items-center rounded-lg bg-[#006437] px-5 text-sm font-semibold text-white transition hover:bg-[#004d2a] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#006437]/20 active:bg-[#004d2a] dark:bg-[#0f7a48] dark:hover:bg-[#0c6a3f]">
            Cadastrar disciplina
        </a>
    </div>

    @include('partials.flash')

    <section aria-label="Lista de disciplinas" class="overflow-hidden rounded-2xl border border-[#d9e1d9] bg-white dark:border-white/10 dark:bg-[#102319]">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] border-collapse text-left">
                <caption class="sr-only">Disciplinas cadastradas</caption>
                <thead>
                    <tr class="border-b border-[#eef2ee] bg-[#f7faf7] dark:border-white/10 dark:bg-white/[0.03]">
                        <th scope="col" class="px-5 py-3 text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Código</th>
                        <th scope="col" class="px-5 py-3 text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Disciplina</th>
                        <th scope="col" class="px-5 py-3 text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Curso</th>
                        <th scope="col" class="px-5 py-3 text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Atividades</th>
                        <th scope="col" class="px-5 py-3 text-right text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($disciplines as $discipline)
                        <tr class="border-b border-[#eef2ee] transition last:border-b-0 hover:bg-[#f7faf7] dark:border-white/10 dark:hover:bg-white/[0.04]">
                            <td class="px-5 py-4 align-middle text-sm font-semibold text-[#006437] dark:text-[#73c69b]">{{ $discipline->class_code }}</td>
                            <td class="px-5 py-4 align-middle text-sm">
                                <span class="font-semibold text-[#172019] dark:text-white">{{ $discipline->label }}</span>
                            </td>
                            <td class="px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">{{ $discipline->course->label }}</td>
                            <td class="px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">{{ $discipline->activities_count }} vinculadas</td>
                            <td class="px-5 py-4 text-right align-middle">
                                <span class="inline-flex items-center gap-3 text-xs font-semibold">
                                    <a href="{{ route('cti.disciplines.edit', $discipline) }}" class="text-[#006437] transition hover:underline dark:text-[#73c69b]">Editar</a>
                                    <form method="POST" action="{{ route('cti.disciplines.destroy', $discipline) }}" onsubmit="return confirm('Excluir {{ $discipline->label }}? A ação não pode ser desfeita.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[#a4262c] transition hover:underline dark:text-[#f2a3a5]">Excluir</button>
                                    </form>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-[#647168] dark:text-white/55">Nenhuma disciplina cadastrada ainda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <p class="mt-4 flex items-start gap-2 text-xs leading-5 text-[#647168] dark:text-white/60">
        <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="12" cy="12" r="9" />
            <path stroke-linecap="round" d="M12 11v5m0-8.5v.5" />
        </svg>
        <span>A disciplina só pode ser excluída quando não tem atividades vinculadas a processos RED.</span>
    </p>
@endsection
