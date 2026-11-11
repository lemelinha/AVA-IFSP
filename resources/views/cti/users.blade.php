@extends('layouts.app')

@section('title', 'Usuários — Área CTI')

@section('content')
    <x-page-header
        title="Usuários"
        subtitle="Gerencie cadastros de alunos, professores, coordenadores e usuários da CTI."
    >
        <x-slot:actions>
            <x-button href="{{ route('cti.users.create') }}">
                Cadastrar usuário
            </x-button>
        </x-slot:actions>
    </x-page-header>

    @include('partials.flash')

    <x-search-bar
        :action="route('cti.users.index')"
        placeholder="Buscar por nome, prontuário ou e-mail"
        :filters="[
            [
                'name' => 'role',
                'label' => 'Perfil',
                'placeholder' => 'Todos os perfis',
                'options' => $roles->pluck('label', 'name')->all(),
            ],
        ]"
        :values="$filters"
    />

    <div class="mt-5">
        <x-data-table
            aria-label="Lista de usuários"
            :columns="[
                'Usuário',
                'E-mail',
                'Perfis',
                'Status',
                ['label' => 'Ações', 'align' => 'right'],
            ]"
            :empty-message="'Nenhum usuário encontrado.'"
            :is-empty="$users->isEmpty()"
            min-width="820px"
        >
            @foreach ($users as $row)
                <tr class="border-b border-[#eef2ee] transition last:border-b-0 hover:bg-[#f7faf7] dark:border-white/10 dark:hover:bg-white/[0.04]">
                    <td class="px-5 py-4 align-middle text-sm">
                        <span class="font-semibold text-[#172019] dark:text-white">{{ $row->name }}</span>
                        <span class="mt-0.5 block text-xs font-normal text-[#647168] dark:text-white/55">Prontuário {{ $row->prontuario }}</span>
                    </td>
                    <td class="px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">{{ $row->email }}</td>
                    <td class="px-5 py-4 align-middle">
                        <x-role-badges :roles="$row->roles" />
                    </td>
                    <td class="px-5 py-4 align-middle">
                        <x-badge :status="$row->blocked ? 'blocked' : 'active'" />
                    </td>
                    <td class="px-5 py-4 text-right align-middle">
                        <span class="inline-flex items-center gap-3 text-xs font-semibold">
                            <x-button variant="link" href="{{ route('cti.users.edit', $row) }}">Editar</x-button>

                            @if ($row->blocked)
                                <x-button variant="link" action="{{ route('cti.users.unblock', $row) }}" method="PATCH">Desbloquear</x-button>
                            @else
                                <x-button
                                    variant="warning"
                                    action="{{ route('cti.users.block', $row) }}"
                                    method="PATCH"
                                    confirm="Bloquear {{ $row->name }}? O usuário não conseguirá entrar no sistema."
                                >
                                    Bloquear
                                </x-button>
                            @endif

                            <x-button
                                variant="danger"
                                action="{{ route('cti.users.destroy', $row) }}"
                                method="DELETE"
                                confirm="Excluir {{ $row->name }}? A ação não pode ser desfeita."
                            >
                                Excluir
                            </x-button>
                        </span>
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </div>
@endsection
