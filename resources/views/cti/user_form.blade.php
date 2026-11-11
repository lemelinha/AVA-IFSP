@extends('layouts.app')

@section('title', isset($user) && $user ? 'Editar usuário — Área CTI' : 'Cadastrar usuário — Área CTI')

@php
    $isEdit = isset($user) && $user !== null;
    $selectedRoles = $isEdit ? $user->roles->pluck('name')->all() : [];
@endphp

@section('content')
    <div>
        <a href="{{ route('cti.users.index') }}" class="text-xs font-semibold text-[#647168] transition hover:text-[#006437] focus:outline-none dark:text-white/65 dark:hover:text-[#73c69b]">← Usuários</a>
        <h1 class="mt-3 text-[28px] font-semibold tracking-tight text-[#172019] sm:text-3xl dark:text-white">
            {{ $isEdit ? 'Editar usuário' : 'Cadastrar usuário' }}
        </h1>
        <p class="mt-1.5 text-sm leading-6 text-[#647168] dark:text-white/65">
            {{ $isEdit ? 'Atualize os dados, a senha e os perfis de acesso do usuário.' : 'Defina os dados de acesso e os perfis do novo usuário.' }}
        </p>
    </div>

    @include('partials.flash')

    <form
        method="POST"
        action="{{ $isEdit ? route('cti.users.update', $user) : route('cti.users.store') }}"
        class="mt-7 max-w-2xl rounded-2xl border border-[#d9e1d9] bg-white p-6 dark:border-white/10 dark:bg-[#102319]"
    >
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Nome completo</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name', $isEdit ? $user->name : '') }}"
                    required
                    autocomplete="name"
                    class="mt-2 h-11 w-full rounded-lg border border-[#d9e1d9] bg-white px-3.5 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10"
                >
            </div>

            <div>
                <label for="prontuario" class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Prontuário</label>
                <input
                    id="prontuario"
                    name="prontuario"
                    type="text"
                    value="{{ old('prontuario', $isEdit ? $user->prontuario : '') }}"
                    required
                    placeholder="JC000000"
                    autocomplete="off"
                    class="mt-2 h-11 w-full rounded-lg border border-[#d9e1d9] bg-white px-3.5 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10"
                >
            </div>

            <div>
                <label for="email" class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">E-mail</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email', $isEdit ? $user->email : '') }}"
                    required
                    autocomplete="email"
                    class="mt-2 h-11 w-full rounded-lg border border-[#d9e1d9] bg-white px-3.5 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10"
                >
            </div>

            <div class="sm:col-span-2">
                <label for="password" class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">
                    Senha {{ $isEdit ? '(se quiser trocar)' : '' }}
                </label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required="{{ $isEdit ? false : true }}"
                    autocomplete="{{ $isEdit ? 'new-password' : 'new-password' }}"
                    class="mt-2 h-11 w-full rounded-lg border border-[#d9e1d9] bg-white px-3.5 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10"
                >
            </div>
        </div>

        <fieldset class="mt-7">
            <legend class="text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Perfis de acesso</legend>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                @foreach ($roles as $role)
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-[#d9e1d9] px-4 py-3 text-sm transition hover:border-[#006437]/40 dark:border-white/10 dark:hover:border-[#73c69b]/40">
                        <input
                            type="checkbox"
                            name="roles[]"
                            value="{{ $role->name }}"
                            @checked(in_array($role->name, $selectedRoles))
                            class="h-4 w-4 accent-[#006437] dark:accent-[#73c69b]"
                        >
                        <span class="font-medium text-[#172019] dark:text-white">{{ $role->label }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div class="mt-7 flex items-center justify-end gap-3">
            <a href="{{ route('cti.users.index') }}" class="h-11 inline-flex items-center rounded-lg border border-[#d9e1d9] px-5 text-sm font-semibold text-[#647168] transition hover:border-[#d12a2e] hover:text-[#d12a2e] dark:border-white/10 dark:text-white/70 dark:hover:border-[#d12a2e] dark:hover:text-[#f2a3a5]">
                Cancelar
            </a>
            <button type="submit" class="h-11 rounded-lg bg-[#006437] px-5 text-sm font-semibold text-white transition hover:bg-[#004d2a] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#006437]/20 active:bg-[#004d2a] dark:bg-[#0f7a48] dark:hover:bg-[#0c6a3f]">
                {{ $isEdit ? 'Salvar alterações' : 'Cadastrar usuário' }}
            </button>
        </div>
    </form>
@endsection
