@extends('layouts.app')

@section('title', isset($discipline) && $discipline ? 'Editar disciplina — Área CTI' : 'Cadastrar disciplina — Área CTI')

@php
    $isEdit = isset($discipline) && $discipline !== null;
@endphp

@section('content')
    <x-form-panel
        :title="$isEdit ? 'Editar disciplina' : 'Cadastrar disciplina'"
        subtitle="Escolha o curso ao qual a disciplina pertence; o coordenador a encontrará ao vincular ao processo RED."
        :back="route('cti.disciplines.index')"
        back-label="Disciplinas"
        :action="$isEdit ? route('cti.disciplines.update', $discipline) : route('cti.disciplines.store')"
        method="POST"
    >
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-form.select
                    name="course_id"
                    label="Curso"
                    :options="$courses->pluck('label', 'id')->all()"
                    :selected="$isEdit ? $discipline->course_id : null"
                    placeholder="Selecione o curso"
                    required
                />
            </div>

            <x-form.input
                name="class_code"
                label="Código da disciplina"
                :value="$isEdit ? $discipline->class_code : null"
                placeholder="Ex.: N00123A"
                required
                autocomplete="off"
            />

            <x-form.input
                name="label"
                label="Nome da disciplina"
                :value="$isEdit ? $discipline->label : null"
                placeholder="Ex.: Matemática"
                required
                autocomplete="off"
            />
        </div>

        <fieldset class="mt-7">
            <legend class="text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Professores responsáveis</legend>
            <p class="mt-1 text-xs leading-5 text-[#647168] dark:text-white/60">Defina os professores que podem publicar atividades nesta disciplina.</p>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                @forelse ($professors as $professor)
                    <x-form.checkbox
                        name="professors[]"
                        :value="$professor->id"
                        :label="$professor->name"
                        :checked="$isEdit && $discipline->professors->contains($professor->id)"
                    />
                @empty
                    <p class="text-sm text-[#647168] dark:text-white/60">Nenhum professor cadastrado. Cadastre professores antes de vinculá-los.</p>
                @endforelse
            </div>
        </fieldset>

        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('cti.disciplines.index') }}">Cancelar</x-button>
            <x-button type="submit">{{ $isEdit ? 'Salvar alterações' : 'Cadastrar disciplina' }}</x-button>
        </x-slot:actions>
    </x-form-panel>
@endsection
