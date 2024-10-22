@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'لیست همه نقش ها']]" />
        @can('write_role')
            <x-create-button route="admin.roles.create" title=" نقش جدید" />
        @endcan
    </div>

    <x-card>
        <x-slot name="cardTitle">لیست همه نقش ها ({{ $roles->total() }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        @php($tableTh = ['ردیف', 'نام', 'نام قابل مشاهده', 'تاریخ ثبت', 'عملیات'])
                        @foreach ($tableTh as $th)
                            <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse($roles as $role)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $role->name }}</td>
                            <td>{{ $role->label ?? '-' }}</td>
                            <td>{{ verta($role->created_at)->format('Y/m/d H:i') }}</td>
                            <td>
                                @can('modify_role')
                                    @include('core::includes.edit-icon-button', [
                                        'model' => $role,
                                        'route' => 'admin.roles.edit',
                                    ])
                                @endcan
                                @can('delete_role')
                                    @include('core::includes.delete-icon-button', [
                                        'model' => $role,
                                        'route' => 'admin.roles.destroy',
                                        'disabled' => false,
                                    ])
                                @endcan
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 5])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $roles->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>
@endsection
