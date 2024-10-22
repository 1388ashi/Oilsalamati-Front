@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'لیست واحد ها']]" />
        @can('create units')
            <x-create-button type="modal" target="createUnitModal" title="واحد جدید" />
        @endcan
    </div>

    <x-card>
        <x-slot name="cardTitle">واحد ها ({{ $units->total() }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        @php($tableTh = ['ردیف', 'نام', 'وضعیت', 'تاریخ ثبت', 'عملیات'])
                        @foreach ($tableTh as $th)
                            <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse($units as $unit)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $unit->name }}</td>
                            <td>@include('core::includes.status', ['status' => $unit->status])</td>
                            <td>{{ verta($unit->created_at)->format('Y/m/d H:i') }}</td>
                            <td>
                                @can('edit units')
                                    @include('core::includes.edit-modal-button', [
                                        'target' => '#edit-unit-' . $unit->id,
                                    ])
                                @endcan
                                @can('delete units')
                                    @include('core::includes.delete-icon-button', [
                                        'model' => $unit,
                                        'route' => 'admin.units.destroy',
                                    ])
                                @endcan
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 7])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $units->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>

    @include('unit::admin.edit')
    @include('unit::admin.create')
@endsection
