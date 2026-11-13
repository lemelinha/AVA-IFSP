@extends('layouts.app')

@section('title', 'Editar vínculos — ' . $process->public_id)

@section('content')
    <x-form-panel
        title="Editar vínculos do processo"
        :subtitle="'Aluno: ' . $process->student->name . ' · Prontuário ' . $process->student->prontuario"
        :back="route('coordinator.processes.show', $process)"
        back-label="Processo"
        :action="route('coordinator.processes.update', $process)"
        method="PUT"
    >
        <div class="rounded-xl border border-[#d9e1d9] bg-[#f7faf7] px-4 py-3 text-sm leading-5 text-[#647168] dark:border-white/10 dark:bg-white/[0.03] dark:text-white/65">
            Altere as disciplinas e os professores responsáveis enquanto o processo estiver ativo.
            Disciplinas que já possuem atividades não podem ser removidas.
        </div>

        <fieldset class="mt-6">
            <legend class="text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Disciplinas e professores</legend>

            @if ($disciplines->isEmpty())
                <p class="mt-4 text-sm text-[#647168] dark:text-white/60">
                    Nenhuma disciplina vinculada ao curso deste aluno.
                </p>
            @else
                <div class="mt-4 space-y-5">
                    @foreach ($disciplines->groupBy('course_id') as $courseDisciplines)
                        <div>
                            <p class="text-sm font-semibold text-[#172019] dark:text-white">{{ $courseDisciplines->first()->course->label }}</p>

                            <div class="mt-2 space-y-3">
                                @foreach ($courseDisciplines as $discipline)
                                    @php
                                        $selected = $selectedDisciplines->get($discipline->id);
                                        $locked = in_array($discipline->id, $lockedDisciplineIds, true);
                                    @endphp

                                    <div class="rounded-xl border border-[#d9e1d9] p-4 dark:border-white/10">
                                        <label class="flex items-center gap-3 text-sm">
                                            <input
                                                type="checkbox"
                                                name="disciplines[]"
                                                value="{{ $discipline->id }}"
                                                @checked($selected !== null)
                                                @disabled($locked)
                                                class="h-4 w-4 accent-[#006437] disabled:cursor-not-allowed disabled:opacity-60 dark:accent-[#73c69b]"
                                            >
                                            @if ($locked)
                                                <input type="hidden" name="disciplines[]" value="{{ $discipline->id }}">
                                            @endif
                                            <span class="font-medium text-[#172019] dark:text-white">{{ $discipline->label }}</span>
                                            <span class="text-xs text-[#647168] dark:text-white/55">{{ $discipline->class_code }}</span>
                                            @if ($locked)
                                                <span class="rounded-full bg-[#eef2ee] px-2 py-0.5 text-[10px] font-semibold text-[#647168] dark:bg-white/10 dark:text-white/60">Com atividades</span>
                                            @endif
                                        </label>

                                        <div class="mt-3">
                                            <x-form.select
                                                :name="'professors['.$discipline->id.']'"
                                                label="Professor"
                                                :options="$discipline->professors->pluck('name', 'id')->all()"
                                                :selected="$selected?->pivot?->professor_id"
                                                placeholder="Selecione o professor"
                                            />
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </fieldset>

        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('coordinator.processes.show', $process) }}">Cancelar</x-button>
            <x-button type="submit">Salvar vínculos</x-button>
        </x-slot:actions>
    </x-form-panel>
@endsection
