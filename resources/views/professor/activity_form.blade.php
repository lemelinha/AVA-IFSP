@extends('layouts.app')

@section('title', isset($activity) && $activity ? 'Editar atividade — Professor' : 'Nova atividade — Professor')

@php
    $isEdit = isset($activity) && $activity !== null;
@endphp

@section('content')
    <x-form-panel
        :title="$isEdit ? 'Editar atividade' : 'Nova atividade'"
        subtitle="Defina o enunciado, o arquivo e o prazo da atividade para o aluno."
        :back="$isEdit ? route('professor.processes.show', $activity->process) : route('professor.processes.index')"
        back-label="Voltar"
        :action="$isEdit ? route('professor.activities.update', $activity) : route('professor.activities.store')"
        method="POST"
    >
        @if (! $isEdit)
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-form.select
                        name="process_id"
                        label="Processo"
                        :options="$processes->mapWithKeys(fn ($p) => [$p->id => $p->public_id.' — '.$p->student->name])->all()"
                        :selected="$process?->id"
                        placeholder="Selecione o processo"
                        required
                    />
                </div>

                <div class="sm:col-span-2">
                    <x-form.select
                        name="discipline_id"
                        label="Disciplina"
                        :options="$disciplines->pluck('label', 'id')->all()"
                        :selected="$discipline?->id"
                        placeholder="Selecione a disciplina"
                        required
                    />
                </div>
            </div>
        @else
            <div class="rounded-xl border border-[#d9e1d9] bg-[#f7faf7] p-4 text-sm dark:border-white/10 dark:bg-white/[0.03]">
                <p class="font-semibold text-[#172019] dark:text-white">{{ $activity->process->public_id }}</p>
                <p class="mt-0.5 text-xs text-[#647168] dark:text-white/55">
                    Aluno: {{ $activity->process->student->name }} · Disciplina: {{ $activity->discipline->label }}
                </p>
            </div>
        @endif

        <div class="mt-7 grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-form.input
                    name="title"
                    label="Título da atividade"
                    :value="$isEdit ? $activity->title : null"
                    placeholder="Ex.: 2ª atividade de Matemática"
                    required
                    autocomplete="off"
                />
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Descrição / enunciado</label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    required
                    class="mt-2 w-full rounded-lg border border-[#d9e1d9] bg-white px-3.5 py-2.5 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10 @error('description') border-[#a4262c] focus:border-[#a4262c] focus:ring-[#a4262c]/10 dark:border-[#f2a3a5] dark:focus:border-[#f2a3a5] dark:focus:ring-[#f2a3a5]/10 @enderror"
                >{{ old('description', $isEdit ? $activity->description : '') }}</textarea>
                @error('description')
                    <p class="mt-1.5 text-xs font-medium text-[#a4262c] dark:text-[#f2a3a5]">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="activity_file" class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">
                    Arquivo da atividade (PDF) {{ $isEdit ? '(envie para substituir)' : '' }}
                </label>
                @if ($isEdit && $activity->url_activity)
                    <p class="mt-1 text-xs text-[#647168] dark:text-white/55">
                        Arquivo atual: <a href="{{ \Illuminate\Support\Facades\Storage::url($activity->url_activity) }}" class="text-[#006437] hover:underline dark:text-[#73c69b]" target="_blank">{{ $activity->url_activity }}</a>
                    </p>
                @endif
                <input
                    id="activity_file"
                    name="activity_file"
                    type="file"
                    accept="application/pdf"
                    @if (! $isEdit) required @endif
                    class="mt-2 block w-full text-sm text-[#172019] file:mr-4 file:rounded-lg file:border-0 file:bg-[#006437] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#004d2a] dark:text-white dark:file:bg-[#0f7a48] dark:hover:file:bg-[#0c6a3f]"
                >
                @error('activity_file')
                    <p class="mt-1.5 text-xs font-medium text-[#a4262c] dark:text-[#f2a3a5]">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <x-form.input
                    name="deadline"
                    label="Prazo de entrega"
                    type="date"
                    :value="$isEdit ? $activity->deadline?->format('Y-m-d') : null"
                    required
                />
            </div>
        </div>

        <x-slot:actions>
            <x-button variant="secondary" href="{{ $isEdit ? route('professor.processes.show', $activity->process) : route('professor.processes.index') }}">Cancelar</x-button>
            <x-button type="submit">{{ $isEdit ? 'Salvar alterações' : 'Criar atividade' }}</x-button>
        </x-slot:actions>
    </x-form-panel>
@endsection