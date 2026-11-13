@extends('layouts.app')

@section('title', isset($student) && $student ? 'Editar aluno — Coordenação' : 'Cadastrar aluno — Coordenação')

@php
    $isEdit = isset($student) && $student !== null;
    $selectedCourse = $isEdit ? $student->courses->first()?->id : null;
@endphp

@section('content')
    <x-form-panel
        :title="$isEdit ? 'Editar aluno' : 'Cadastrar aluno'"
        subtitle="Cadastre o aluno no Regime de Ensino a Distância e vincule ao seu curso."
        :back="route('coordinator.students.index')"
        back-label="Alunos"
        :action="$isEdit ? route('coordinator.students.update', $student) : route('coordinator.students.store')"
        method="{{ $isEdit ? 'PUT' : 'POST' }}"
    >
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-form.input
                    name="name"
                    label="Nome completo"
                    :value="$isEdit ? $student->name : null"
                    required
                    autocomplete="name"
                />
            </div>

            <x-form.input
                name="prontuario"
                label="Prontuário"
                :value="$isEdit ? $student->prontuario : null"
                placeholder="JC000000"
                required
                autocomplete="off"
            />

            <x-form.input
                name="email"
                label="E-mail"
                type="email"
                :value="$isEdit ? $student->email : null"
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

            <div class="sm:col-span-2">
                <x-form.select
                    name="course_id"
                    label="Curso"
                    :options="$courses->pluck('label', 'id')->all()"
                    :selected="$selectedCourse"
                    placeholder="Selecione o curso"
                    required
                />
            </div>
        </div>

        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('coordinator.students.index') }}">Cancelar</x-button>
            <x-button type="submit">{{ $isEdit ? 'Salvar alterações' : 'Cadastrar aluno' }}</x-button>
        </x-slot:actions>
    </x-form-panel>
@endsection
