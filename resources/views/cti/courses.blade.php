@extends('layouts.app')

@section('title', 'Cursos — Área CTI')

@section('content')
    <x-page-header
        title="Cursos"
        subtitle="Cadastre os cursos técnicos do campus. Use as disciplinas para distribuir as matérias de cada curso."
    >
        <x-slot:actions>
            <x-button href="{{ route('cti.courses.create') }}">
                Cadastrar curso
            </x-button>
        </x-slot:actions>
    </x-page-header>

    @include('partials.flash')

    <x-search-bar
        :action="route('cti.courses.index')"
        placeholder="Buscar por nome ou código do curso"
        :values="$filters"
    />

    <div class="mt-5">
        <x-data-table
            aria-label="Lista de cursos"
            :columns="[
                'Código',
                'Curso',
                'Disciplinas',
                ['label' => 'Ações', 'align' => 'right'],
            ]"
            :empty-message="'Nenhum curso cadastrado ainda.'"
            :is-empty="$courses->isEmpty()"
            min-width="640px"
        >
            @foreach ($courses as $course)
                <tr class="border-b border-[#eef2ee] transition last:border-b-0 hover:bg-[#f7faf7] dark:border-white/10 dark:hover:bg-white/[0.04]">
                    <td class="px-5 py-4 align-middle text-sm font-semibold text-[#006437] dark:text-[#73c69b]">{{ $course->course_code }}</td>
                    <td class="px-5 py-4 align-middle text-sm">
                        <span class="font-semibold text-[#172019] dark:text-white">{{ $course->label }}</span>
                    </td>
                    <td class="px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">{{ $course->disciplines_count }} vinculadas</td>
                    <td class="px-5 py-4 text-right align-middle">
                        <span class="inline-flex items-center gap-3 text-xs font-semibold">
                            <x-button variant="link" href="{{ route('cti.courses.edit', $course) }}">Editar</x-button>
                            <x-button
                                variant="danger"
                                action="{{ route('cti.courses.destroy', $course) }}"
                                method="DELETE"
                                confirm="Excluir {{ $course->label }}? A ação não pode ser desfeita."
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
        O curso só pode ser excluído quando não tem disciplinas vinculadas.
    </x-hint>
@endsection
