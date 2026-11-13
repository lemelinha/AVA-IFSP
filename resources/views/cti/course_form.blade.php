@extends('layouts.app')

@section('title', isset($course) && $course ? 'Editar curso — Área CTI' : 'Cadastrar curso — Área CTI')

@php
    $isEdit = isset($course) && $course !== null;
@endphp

@section('content')
    <x-form-panel
        :title="$isEdit ? 'Editar curso' : 'Cadastrar curso'"
        subtitle="Os cursos aparecem para os coordenadores e alunos quando os processos RED são criados."
        :back="route('cti.courses.index')"
        back-label="Cursos"
        :action="$isEdit ? route('cti.courses.update', $course) : route('cti.courses.store')"
        method="{{ $isEdit ? 'PUT' : 'POST' }}"
    >
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.input
                name="course_code"
                label="Código do curso"
                :value="$isEdit ? $course->course_code : null"
                placeholder="Ex.: 10391"
                required
                autocomplete="off"
            />

            <x-form.input
                name="label"
                label="Nome do curso"
                :value="$isEdit ? $course->label : null"
                placeholder="Ex.: Técnico em Informática"
                required
                autocomplete="off"
            />
        </div>

        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('cti.courses.index') }}">Cancelar</x-button>
            <x-button type="submit">{{ $isEdit ? 'Salvar alterações' : 'Cadastrar curso' }}</x-button>
        </x-slot:actions>
    </x-form-panel>
@endsection
