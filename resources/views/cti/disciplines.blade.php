@extends('layouts.app')

@section('title', 'Disciplinas — Área CTI')

@section('content')
    <x-page-header
        title="Disciplinas"
        subtitle="Cada disciplina deve pertencer a um curso e ficar pronta para ser vinculada aos processos RED pelos coordenadores."
    >
        <x-slot:actions>
            <x-button href="{{ route('cti.disciplines.create') }}">
                Cadastrar disciplina
            </x-button>
        </x-slot:actions>
    </x-page-header>

    @include('partials.flash')

    <x-search-bar
        :action="route('cti.disciplines.index')"
        placeholder="Buscar por nome, código ou curso"
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
            aria-label="Lista de disciplinas"
            :columns="[
                'Código',
                'Disciplina',
                'Curso',
                'Atividades',
                ['label' => 'Ações', 'align' => 'right'],
            ]"
            :empty-message="'Nenhuma disciplina cadastrada ainda.'"
            :is-empty="$disciplines->isEmpty()"
            min-width="760px"
        >
            @foreach ($disciplines as $discipline)
                <tr class="border-b border-[#eef2ee] transition last:border-b-0 hover:bg-[#f7faf7] dark:border-white/10 dark:hover:bg-white/[0.04]">
                    <td class="px-5 py-4 align-middle text-sm font-semibold text-[#006437] dark:text-[#73c69b]">{{ $discipline->class_code }}</td>
                    <td class="px-5 py-4 align-middle text-sm">
                        <span class="font-semibold text-[#172019] dark:text-white">{{ $discipline->label }}</span>
                    </td>
                    <td class="px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">{{ $discipline->course->label }}</td>
                    <td class="px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">{{ $discipline->activities_count }} vinculadas</td>
                    <td class="px-5 py-4 text-right align-middle">
                        <span class="inline-flex items-center gap-3 text-xs font-semibold">
                            <x-button variant="link" href="{{ route('cti.disciplines.edit', $discipline) }}">Editar</x-button>
                            <x-button
                                variant="danger"
                                action="{{ route('cti.disciplines.destroy', $discipline) }}"
                                method="DELETE"
                                confirm="Excluir {{ $discipline->label }}? A ação não pode ser desfeita."
                            >
                                Excluir
                            </x-button>
                        </span>
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </div>

    <x-hint>
        A disciplina só pode ser excluída quando não tem atividades vinculadas a processos RED.
    </x-hint>
@endsection
