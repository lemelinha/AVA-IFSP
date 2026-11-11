@extends('layouts.app')

@section('title', 'Usuários — Área CTI')

@section('content')
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-[28px] font-semibold tracking-tight text-[#172019] sm:text-3xl dark:text-white">Usuários</h1>
            <p class="mt-1.5 text-sm leading-6 text-[#647168] dark:text-white/65">Gerencie cadastros de alunos, professores, coordenadores e usuários da CTI.</p>
        </div>

        <a href="{{ route('cti.users.create') }}" class="h-11 inline-flex items-center rounded-lg bg-[#006437] px-5 text-sm font-semibold text-white transition hover:bg-[#004d2a] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#006437]/20 active:bg-[#004d2a] dark:bg-[#0f7a48] dark:hover:bg-[#0c6a3f]">
            Cadastrar usuário
        </a>
    </div>

    @include('partials.flash')

    <section aria-label="Lista de usuários" class="overflow-hidden rounded-2xl border border-[#d9e1d9] bg-white dark:border-white/10 dark:bg-[#102319]">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[820px] border-collapse text-left">
                <caption class="sr-only">Usuários cadastrados</caption>
                <thead>
                    <tr class="border-b border-[#eef2ee] bg-[#f7faf7] dark:border-white/10 dark:bg-white/[0.03]">
                        <th scope="col" class="px-5 py-3 text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Usuário</th>
                        <th scope="col" class="px-5 py-3 text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">E-mail</th>
                        <th scope="col" class="px-5 py-3 text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Perfis</th>
                        <th scope="col" class="px-5 py-3 text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Status</th>
                        <th scope="col" class="px-5 py-3 text-right text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $row)
                        <tr class="border-b border-[#eef2ee] transition last:border-b-0 hover:bg-[#f7faf7] dark:border-white/10 dark:hover:bg-white/[0.04]">
                            <td class="px-5 py-4 align-middle text-sm">
                                <span class="font-semibold text-[#172019] dark:text-white">{{ $row->name }}</span>
                                <span class="mt-0.5 block text-xs font-normal text-[#647168] dark:text-white/55">Prontuário {{ $row->prontuario }}</span>
                            </td>
                            <td class="px-5 py-4 align-middle text-sm">{{ $row->email }}</td>
                            <td class="px-5 py-4 align-middle">
                                <x-role-badges :roles="$row->roles" />
                            </td>
                            <td class="px-5 py-4 align-middle">
                                <x-badge :status="$row->blocked ? 'blocked' : 'active'" />
                            </td>
                            <td class="px-5 py-4 text-right align-middle">
                                <span class="inline-flex items-center gap-3 text-xs font-semibold">
                                    <a href="{{ route('cti.users.edit', $row) }}" class="text-[#006437] transition hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#006437]/40 dark:text-[#73c69b]">
                                        Editar
                                    </a>
                                    @if ($row->blocked)
                                        <form method="POST" action="{{ route('cti.users.unblock', $row) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-[#006437] transition hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#006437]/40 dark:text-[#73c69b]">Desbloquear</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('cti.users.block', $row) }}" onsubmit="return confirm('Bloquear {{ $row->name }}? O usuário não conseguirá entrar no sistema.')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-[#8a5a00] transition hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8a5a00]/40 dark:text-[#e8c07a]">Bloquear</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('cti.users.destroy', $row) }}" onsubmit="return confirm('Excluir {{ $row->name }}? A ação não pode ser desfeita.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[#a4262c] transition hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#a4262c]/40 dark:text-[#f2a3a5]">Excluir</button>
                                    </form>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-[#647168] dark:text-white/55">Nenhum usuário cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
