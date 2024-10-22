@extends('admin.layouts.master')
@section('content')
<div class="page-header">
    <x-breadcrumb :items="[['title' => 'صفحات']]" />
    @can('write_page')
        <x-create-button route="admin.pages.create" title="صفحه جدید" />
    @endcan
</div>

<!-- row opened -->
<x-card>
    <x-slot name="cardTitle">صفحات ({{ number_format($pages->total()) }})</x-slot>
    <x-slot name="cardOptions"><x-card-options /></x-slot>
    <x-slot name="cardBody">
        @include('components.errors')
        <x-table-component>
            <x-slot name="tableTh">
                <tr>
                    @php($tableTh = ['ردیف', 'عنوان', 'تاریخ ثبت', 'عملیات'])
                    @foreach ($tableTh as $th)
                        <th>{{ $th }}</th>
                    @endforeach
                </tr>
            </x-slot>
            <x-slot name="tableTd">
                @forelse($pages as $page)
                    <tr>
                        <td class="font-weight-bold">{{ $loop->iteration }}</td>
                        <td>{{ $page->title }}</td>
                        <td>{{ verta($page->created_at)->format('Y/m/d H:i') }}</td>
                        <td>
                            @include('core::includes.edit-icon-button', [
                                'model' => $page,
                                'route' => 'admin.pages.edit',
                            ])
                            @can('delete_page')
                                @include('core::includes.delete-icon-button', [
                                    'model' => $page,
                                    'route' => 'admin.pages.destroy',
                                ])
                            @endcan
                        </td>
                    </tr>
                @empty
                    @include('core::includes.data-not-found-alert', ['colspan' => 4])
                @endforelse
            </x-slot>
            <x-slot name="extraData">{{ $pages->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
        </x-table-component>
    </x-slot>
</x-card>
@endsection
