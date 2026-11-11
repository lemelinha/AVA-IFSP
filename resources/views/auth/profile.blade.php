@extends('layouts.app')

@section('title', 'Perfil — ' . config('app.name'))

@section('content')
    <div>
        <h1 class="text-[28px] font-semibold tracking-tight text-[#172019] sm:text-3xl dark:text-white">Meu perfil</h1>
        <p class="mt-1.5 text-sm leading-6 text-[#647168] dark:text-white/65">Dados da sua conta no Sistema RED.</p>
    </div>

    <section aria-label="Dados do perfil" class="mt-7 overflow-hidden rounded-2xl border border-[#d9e1d9] bg-white dark:border-white/10 dark:bg-[#102319]">
        <div class="flex flex-col gap-6 border-b border-[#d9e1d9] p-6 dark:border-white/10 sm:flex-row sm:items-center sm:p-8">
            <span class="flex h-20 w-20 shrink-0 items-center justify-center bg-[#006437] text-2xl font-semibold text-white dark:bg-[#0f7a48]" aria-hidden="true">
                {{ auth()->user()->initials() }}
            </span>

            <div class="min-w-0">
                <h2 class="truncate text-xl font-semibold text-[#172019] dark:text-white">{{ auth()->user()->name }}</h2>
                <p class="mt-1 text-sm text-[#647168] dark:text-white/65">Prontuário {{ auth()->user()->prontuario }}</p>

                <div class="mt-3 flex flex-wrap gap-2">
                    <x-role-badges :roles="auth()->user()->roles" />
                </div>
            </div>
        </div>

        <dl class="grid gap-6 p-6 sm:grid-cols-2 sm:p-8">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-[#647168] dark:text-white/45">Nome completo</dt>
                <dd class="mt-2 text-sm leading-6 text-[#172019] dark:text-white">{{ auth()->user()->name }}</dd>
            </div>

            <div>
                <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-[#647168] dark:text-white/45">Prontuário</dt>
                <dd class="mt-2 text-sm leading-6 text-[#172019] dark:text-white">{{ auth()->user()->prontuario }}</dd>
            </div>

            <div>
                <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-[#647168] dark:text-white/45">E-mail</dt>
                <dd class="mt-2 text-sm leading-6 text-[#172019] dark:text-white">{{ auth()->user()->email }}</dd>
            </div>

            <div>
                <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-[#647168] dark:text-white/45">Membro desde</dt>
                <dd class="mt-2 text-sm leading-6 text-[#172019] dark:text-white">
                    {{ auth()->user()->created_at->translatedFormat('d \d\e F \d\e Y') }}
                </dd>
            </div>
        </dl>
    </section>
@endsection
