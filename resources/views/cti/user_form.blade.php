@extends('layouts.app')

@section('title', isset($user) && $user ? 'Editar usuário — Área CTI' : 'Cadastrar usuário — Área CTI')

@php
    $isEdit = isset($user) && $user !== null;
    $selectedRoles = $isEdit ? $user->roles->pluck('name')->all() : [];
    $selectedCourses = $isEdit ? $user->coordinatedCourses()->pluck('courses.id')->all() : [];
@endphp

@section('content')
    <x-form-panel
        :title="$isEdit ? 'Editar usuário' : 'Cadastrar usuário'"
        :subtitle="$isEdit ? 'Atualize os dados, a senha e os perfis de acesso do usuário.' : 'Defina os dados de acesso e os perfis do novo usuário.'"
        :back="route('cti.users.index')"
        back-label="Usuários"
        :action="$isEdit ? route('cti.users.update', $user) : route('cti.users.store')"
        method="POST"
    >
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-form.input
                    name="name"
                    label="Nome completo"
                    :value="$isEdit ? $user->name : null"
                    required
                    autocomplete="name"
                />
            </div>

            <x-form.input
                name="prontuario"
                label="Prontuário"
                :value="$isEdit ? $user->prontuario : null"
                placeholder="JC000000"
                required
                autocomplete="off"
            />

            <x-form.input
                name="email"
                label="E-mail"
                type="email"
                :value="$isEdit ? $user->email : null"
                required
                autocomplete="email"
            />

            <div class="sm:col-span-2">
                <x-form.input
                    name="password"
                    label="Senha {{ $isEdit ? '(se quiser trocar)' : '' }}"
                    type="password"
                    :required="! $isEdit"
                    autocomplete="new-password"
                />
            </div>
        </div>

        <fieldset class="mt-7">
            <legend class="text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Perfis de acesso</legend>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                @foreach ($roles as $role)
                    <x-form.checkbox
                        name="roles[]"
                        :value="$role->name"
                        :label="$role->label"
                        :checked="in_array($role->name, $selectedRoles)"
                    />
                @endforeach
            </div>
        </fieldset>

        <fieldset class="mt-7">
            <legend class="text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Cursos coordenados</legend>
            <p class="mt-1 text-xs leading-5 text-[#647168] dark:text-white/60">Defina os cursos que este coordenador pode abrir processos e cadastrar alunos.</p>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                @forelse ($courses as $course)
                    <x-form.checkbox
                        name="courses[]"
                        :value="$course->id"
                        :label="$course->label"
                        :checked="in_array($course->id, $selectedCourses)"
                    />
                @empty
                    <p class="text-sm text-[#647168] dark:text-white/60">Nenhum curso cadastrado. Cadastre os cursos antes de definir a coordenação.</p>
                @endforelse
            </div>
        </fieldset>

        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('cti.users.index') }}">Cancelar</x-button>
            <x-button type="submit">{{ $isEdit ? 'Salvar alterações' : 'Cadastrar usuário' }}</x-button>
        </x-slot:actions>
    </x-form-panel>
@endsection
