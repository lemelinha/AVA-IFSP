@extends('layouts.app')

@section('title', isset($discipline) && $discipline ? 'Editar disciplina — Área CTI' : 'Cadastrar disciplina — Área CTI')

@php
    $isEdit = isset($discipline) && $discipline !== null;
@endphp

@section('content')
    <div>
        <a href="{{ route('cti.disciplines.index') }}" class="text-xs font-semibold text-[#647168] transition hover:text-[#006437] dark:text-white/65 dark:hover:text-[#73c69b]">← Disciplinas</a>
        <h1 class="mt-3 text-[28px] font-semibold tracking-tight text-[#172019] sm:text-3xl dark:text-white">
            {{ $isEdit ? 'Editar disciplina' : 'Cadastrar disciplina' }}
        </h1>
        <p class="mt-1.5 text-sm leading-6 text-[#647168] dark:text-white/65">Escolha o curso ao qual a disciplina pertence; o coordenador a encontrará ao vincular ao processo RED.</p>
    </div>

    @include('partials.flash')

    <form
        method="POST"
        action="{{ $isEdit ? route('cti.disciplines.update', $discipline) : route('cti.disciplines.store') }}"
        class="mt-7 grid max-w-2xl gap-5 rounded-2xl border border-[#d9e1d9] bg-white p-6 sm:grid-cols-2 dark:border-white/10 dark:bg-[#102319]"
    >
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="sm:col-span-2">
            <label for="course_id" class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Curso</label>
            <div class="relative mt-2">
                <select
                    id="course_id"
                    name="course_id"
                    required
                    class="h-11 w-full appearance-none rounded-lg border border-[#d9e1d9] bg-white px-3.5 pr-9 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10"
                >
                    @php
                        $selectedCourse = old('course_id', $isEdit ? $discipline->course_id : null);
                    @endphp
                    <option value="" disabled {{ $selectedCourse ? '' : 'selected' }}>Selecione o curso</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" @selected($selectedCourse === $course->id)>{{ $course->label }}</option>
                    @endforeach
                </select>
                <svg class="pointer-events-none absolute top-1/2 right-3.5 h-3.5 w-3.5 -translate-y-1/2 text-[#647168] dark:text-white/45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="M6 9l6 6 6-6" />
                </svg>
            </div>
        </div>

        <div>
            <label for="class_code" class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Código da disciplina</label>
            <input
                id="class_code"
                name="class_code"
                type="text"
                value="{{ old('class_code', $isEdit ? $discipline->class_code : '') }}"
                required
                placeholder="Ex.: N00123A"
                autocomplete="off"
                class="mt-2 h-11 w-full rounded-lg border border-[#d9e1d9] bg-white px-3.5 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10"
            >
        </div>

        <div>
            <label for="label" class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Nome da disciplina</label>
            <input
                id="label"
                name="label"
                type="text"
                value="{{ old('label', $isEdit ? $discipline->label : '') }}"
                required
                placeholder="Ex.: Matemática"
                autocomplete="off"
                class="mt-2 h-11 w-full rounded-lg border border-[#d9e1d9] bg-white px-3.5 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10"
            >
        </div>

        <div class="sm:col-span-2 flex items-center justify-end gap-3">
            <a href="{{ route('cti.disciplines.index') }}" class="h-11 inline-flex items-center rounded-lg border border-[#d9e1d9] px-5 text-sm font-semibold text-[#647168] transition hover:border-[#d12a2e] hover:text-[#d12a2e] dark:border-white/10 dark:text-white/70 dark:hover:border-[#d12a2e] dark:hover:text-[#f2a3a5]">
                Cancelar
            </a>
            <button type="submit" class="h-11 rounded-lg bg-[#006437] px-5 text-sm font-semibold text-white transition hover:bg-[#004d2a] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#006437]/20 active:bg-[#004d2a] dark:bg-[#0f7a48] dark:hover:bg-[#0c6a3f]">
                {{ $isEdit ? 'Salvar alterações' : 'Cadastrar disciplina' }}
            </button>
        </div>
    </form>
@endsection
