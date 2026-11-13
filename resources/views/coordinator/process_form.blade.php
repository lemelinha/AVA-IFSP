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
        <div class="space-y-5">
            <div>
                @if ($students->isEmpty())
                    <div class="rounded-xl border border-[#f0dcb0] bg-[#fdf3e0] px-4 py-3 text-sm leading-5 text-[#8a5a00] dark:border-[#e8c07a]/30 dark:bg-[#fdf3e0]/10 dark:text-[#e8c07a]">
                        Nenhum aluno disponível. Todos os alunos dos seus cursos já possuem um processo RED ativo ou ainda não foram vinculados a um curso.
                    </div>
                @else
                    <x-form.combobox
                        name="user_id"
                        label="Aluno"
                        :options="$students->pluck('name', 'id')->all()"
                        :selected="old('user_id')"
                        placeholder="Digite para buscar o aluno"
                        required
                    />
                    <p class="mt-1.5 text-xs leading-5 text-[#647168] dark:text-white/60">
                        Apenas alunos sem processo RED ativo aparecem na lista.
                    </p>
                @endif
            </div>
        </div>

        <fieldset class="mt-7">
            <legend class="text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Disciplinas e professores</legend>
            <p class="mt-1 text-xs leading-5 text-[#647168] dark:text-white/60">
                Selecione as disciplinas de um único curso e o professor responsável por cada uma. O professor publicará as atividades para o aluno.
            </p>

            @if ($disciplines->isEmpty())
                <p class="mt-4 text-sm text-[#647168] dark:text-white/60">
                    Nenhuma disciplina vinculada aos seus cursos. Cadastre disciplinas antes de abrir um processo.
                </p>
            @else
                <div class="mt-4 space-y-5" data-course-groups>
                    @foreach ($disciplines->groupBy('course_id') as $courseDisciplines)
                        <div data-course-group="{{ $courseDisciplines->first()->course_id }}">
                            <p class="text-sm font-semibold text-[#172019] dark:text-white">{{ $courseDisciplines->first()->course->label }}</p>

                            <div class="mt-2 space-y-3">
                                @foreach ($courseDisciplines as $discipline)
                                    <div class="rounded-xl border border-[#d9e1d9] p-4 dark:border-white/10">
                                        <label class="flex items-center gap-3 text-sm">
                                            <input
                                                type="checkbox"
                                                name="disciplines[]"
                                                value="{{ $discipline->id }}"
                                                @checked(in_array($discipline->id, (array) old('disciplines', []), true))
                                                class="h-4 w-4 accent-[#006437] dark:accent-[#73c69b]"
                                            >
                                            <span class="font-medium text-[#172019] dark:text-white">{{ $discipline->label }}</span>
                                            <span class="text-xs text-[#647168] dark:text-white/55">{{ $discipline->class_code }}</span>
                                        </label>

                                        <div class="mt-3">
                                            <x-form.select
                                                :name="'professors['.$discipline->id.']'"
                                                label="Professor"
                                                :options="$discipline->professors->pluck('name', 'id')->all()"
                                                :selected="old('professors.'.$discipline->id)"
                                                placeholder="Selecione o professor"
                                            />
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <p id="course-filter-notice" class="mt-4 hidden text-sm text-[#647168] dark:text-white/60">
                    Nenhuma disciplina cadastrada para o curso deste aluno.
                </p>

                <script>
                    (function () {
                        var studentInput = document.querySelector('[data-combobox-value][name="user_id"]');
                        if (! studentInput) { return; }

                        var studentCourses = @json($studentCourses);
                        var groups = Array.prototype.slice.call(document.querySelectorAll('[data-course-group]'));
                        var notice = document.getElementById('course-filter-notice');

                        function applyFilter() {
                            var allowed = studentCourses[studentInput.value] || null;
                            var visible = 0;

                            groups.forEach(function (group) {
                                var show = ! allowed || allowed.indexOf(group.getAttribute('data-course-group')) !== -1;
                                group.classList.toggle('hidden', ! show);
                                if (show) { visible++; }

                                if (! show) {
                                    group.querySelectorAll('input[type="checkbox"]').forEach(function (checkbox) {
                                        checkbox.checked = false;
                                    });
                                }
                            });

                            if (notice) {
                                notice.classList.toggle('hidden', ! allowed || visible > 0);
                            }
                        }

                        studentInput.addEventListener('change', applyFilter);
                        applyFilter();
                    })();
                </script>
            @endif
        </fieldset>

        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('coordinator.processes.index') }}">Cancelar</x-button>
            <x-button type="submit">Abrir processo</x-button>
        </x-slot:actions>
    </x-form-panel>
@endsection
