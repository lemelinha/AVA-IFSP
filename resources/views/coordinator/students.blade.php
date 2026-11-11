@extends('layouts.app')

@section('title', 'Alunos — Coordenação')

@section('content')
    <x-page-header
        title="Alunos"
        subtitle="Alunos em Regime de Ensino a Distância vinculados aos seus cursos."
    >
        <x-slot:actions>
            <x-button href="{{ route('coordinator.students.create') }}">
                Cadastrar aluno
            </x-button>
        </x-slot:actions>
    </x-page-header>

    @include('partials.flash')

    <x-search-bar
        :action="route('coordinator.students.index')"
        placeholder="Buscar por nome, prontuário ou e-mail"
        :filters="[
            [
                'name' => 'course',
                'label' => 'Curso',
                'placeholder' => 'Todos os cursos',
                'options' => $courses->pluck('label', 'id')->all(),
            ],
        ]"
        :values="$filters"
    />

    <div class="mt-5">
        <x-data-table
            aria-label="Lista de alunos"
            :columns="[
                'Aluno',
                'E-mail',
                'Curso',
                'Processos',
                ['label' => 'Ações', 'align' => 'right'],
            ]"
            :empty-message="'Nenhum aluno cadastrado ainda.'"
            :is-empty="$students->isEmpty()"
            min-width="820px"
        >
            @foreach ($students as $student)
                <tr class="border-b border-[#eef2ee] transition last:border-b-0 hover:bg-[#f7faf7] dark:border-white/10 dark:hover:bg-white/[0.04]">
                    <td class="px-5 py-4 align-middle text-sm">
                        <span class="font-semibold text-[#172019] dark:text-white">{{ $student->name }}</span>
                        <span class="mt-0.5 block text-xs font-normal text-[#647168] dark:text-white/55">Prontuário {{ $student->prontuario }}</span>
                    </td>
                    <td class="px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">{{ $student->email }}</td>
                    <td class="px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">
                        {{ $student->courses->pluck('label')->implode(', ') ?: '—' }}
                    </td>
                    <td class="px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">{{ $student->processes_count }} vínculo(s)</td>
                    <td class="px-5 py-4 text-right align-middle">
                        <span class="inline-flex items-center gap-3 text-xs font-semibold">
                            <x-button variant="link" href="{{ route('coordinator.students.edit', $student) }}">Editar</x-button>
                        </span>
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </div>
@endsection
