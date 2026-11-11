@extends('layouts.app')

@section('title', 'Novo processo RED — Coordenação')

@section('content')
    <x-form-panel
        title="Novo processo RED"
        subtitle="Vincule o aluno às disciplinas e aos professores responsáveis."
        :back="route('coordinator.processes.index')"
        back-label="Processos"
        :action="route('coordinator.processes.store')"
        method="POST"
    >
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-form.combobox
                    name="user_id"
                    label="Aluno"
                    :options="$students->pluck('name', 'id')->all()"
                    :selected="old('user_id')"
                    placeholder="Digite para buscar o aluno"
                    required
                />
            </div>

            <div class="sm:col-span-2">
                <x-form.select
                    name="course_id"
                    label="Curso"
                    :options="$courses->pluck('label', 'id')->all()"
                    placeholder="Selecione o curso"
                    required
                />
            </div>
        </div>

        <fieldset class="mt-7">
            <legend class="text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Disciplinas e professores</legend>
            <p class="mt-1 text-xs leading-5 text-[#647168] dark:text-white/60">
                Selecione as disciplinas do processo e o professor responsável por cada uma. O professor publicará as atividades para o aluno.
            </p>

            @if ($disciplines->isEmpty())
                <p class="mt-4 text-sm text-[#647168] dark:text-white/60">
                    Nenhuma disciplina vinculada aos seus cursos. Cadastre disciplinas antes de abrir um processo.
                </p>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($disciplines as $discipline)
                        <div class="rounded-xl border border-[#d9e1d9] p-4 dark:border-white/10">
                            <label class="flex items-center gap-3 text-sm">
                                <input
                                    type="checkbox"
                                    name="disciplines[]"
                                    value="{{ $discipline->id }}"
                                    class="h-4 w-4 accent-[#006437] dark:accent-[#73c69b]"
                                >
                                <span class="font-medium text-[#172019] dark:text-white">{{ $discipline->label }}</span>
                                <span class="text-xs text-[#647168] dark:text-white/55">{{ $discipline->course->label }}</span>
                            </label>

                            <div class="mt-3">
                                <x-form.select
                                    :name="'professors['.$discipline->id.']'"
                                    label="Professor"
                                    :options="$discipline->professors->pluck('name', 'id')->all()"
                                    placeholder="Selecione o professor"
                                />
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </fieldset>

        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('coordinator.processes.index') }}">Cancelar</x-button>
            <x-button type="submit">Abrir processo</x-button>
        </x-slot:actions>
    </x-form-panel>
@endsection
