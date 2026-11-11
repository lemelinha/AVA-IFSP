@extends('layouts.app')

@section('title', isset($course) && $course ? 'Editar curso — Área CTI' : 'Cadastrar curso — Área CTI')

@php
    $isEdit = isset($course) && $course !== null;
@endphp

@section('content')
    <div>
        <a href="{{ route('cti.courses.index') }}" class="text-xs font-semibold text-[#647168] transition hover:text-[#006437] dark:text-white/65 dark:hover:text-[#73c69b]">← Cursos</a>
        <h1 class="mt-3 text-[28px] font-semibold tracking-tight text-[#172019] sm:text-3xl dark:text-white">
            {{ $isEdit ? 'Editar curso' : 'Cadastrar curso' }}
        </h1>
        <p class="mt-1.5 text-sm leading-6 text-[#647168] dark:text-white/65">Os cursos aparecem para os coordenadores e alunos quando os processos RED são criados.</p>
    </div>

    @include('partials.flash')

    <form
        method="POST"
        action="{{ $isEdit ? route('cti.courses.update', $course) : route('cti.courses.store') }}"
        class="mt-7 grid max-w-2xl gap-5 rounded-2xl border border-[#d9e1d9] bg-white p-6 sm:grid-cols-2 dark:border-white/10 dark:bg-[#102319]"
    >
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div>
            <label for="course_code" class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Código do curso</label>
            <input
                id="course_code"
                name="course_code"
                type="text"
                value="{{ old('course_code', $isEdit ? $course->course_code : '') }}"
                required
                placeholder="Ex.: 10391"
                autocomplete="off"
                class="mt-2 h-11 w-full rounded-lg border border-[#d9e1d9] bg-white px-3.5 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10"
            >
        </div>

        <div>
            <label for="label" class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Nome do curso</label>
            <input
                id="label"
                name="label"
                type="text"
                value="{{ old('label', $isEdit ? $course->label : '') }}"
                required
                placeholder="Ex.: Técnico em Informática"
                autocomplete="off"
                class="mt-2 h-11 w-full rounded-lg border border-[#d9e1d9] bg-white px-3.5 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10"
            >
        </div>

        <div class="sm:col-span-2 flex items-center justify-end gap-3">
            <a href="{{ route('cti.courses.index') }}" class="h-11 inline-flex items-center rounded-lg border border-[#d9e1d9] px-5 text-sm font-semibold text-[#647168] transition hover:border-[#d12a2e] hover:text-[#d12a2e] dark:border-white/10 dark:text-white/70 dark:hover:border-[#d12a2e] dark:hover:text-[#f2a3a5]">
                Cancelar
            </a>
            <button type="submit" class="h-11 rounded-lg bg-[#006437] px-5 text-sm font-semibold text-white transition hover:bg-[#004d2a] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#006437]/20 active:bg-[#004d2a] dark:bg-[#0f7a48] dark:hover:bg-[#0c6a3f]">
                {{ $isEdit ? 'Salvar alterações' : 'Cadastrar curso' }}
            </button>
        </div>
    </form>
@endsection
