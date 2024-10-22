@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'کمپین فروش']]" />
        @can('write_flashes')
            <x-create-button route="admin.flashes.create" title="کمپین جدید" />
        @endcan
    </div>

    <x-card>
        <x-slot name="cardTitle">لیست کمپین های فروش ({{ $flashesCount }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        <th>ردیف</th>
                        <th>عنوان</th>
                        <th>شناسه</th>
                        <th>عکس</th>
                        <th>تاریخ شروع</th>
                        <th>تاریخ پایان</th>
                        <th>وضعیت</th>
                        <th>تاریخ ثبت</th>
                        <th>عملیات</th>
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse ($flashes as $flash)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $flash->title }}</td>
                            <td>{{ $flash->id }}</td>
                            <td>
                                {{-- <div class="bg-azure" style="max-height: 70px; height: 1504px;"><img
                                        src="{{ $flash->image->url }}" />
                                </div> --}}
                            -
                            </td>
                            <td>{{ verta($flash->start_date)->format('Y/m/d H:i') }}</td>
                            <td>{{ verta($flash->end_date)->format('Y/m/d H:i') }}</td>
                            <td>
                                <x-badge isLight="true">
                                    <x-slot name="type">{{ $flash->status ? 'success' : 'danger' }}</x-slot>
                                    <x-slot name="text">{{ $flash->status ? 'فعال' : 'غیر فعال' }}</x-slot>
                                </x-badge>
                            </td>
                            <td>{{ verta($flash->created_at)->format('Y/m/d H:i') }}</td>
                            <td>

                                @can('read_flash')
                                    @include('core::includes.show-icon-button', [
                                        'model' => $flash,
                                        'route' => 'admin.flashes.show',
                                    ])
                                @endcan

                                @can('modify_flash')
                                    @include('core::includes.edit-icon-button', [
                                        'model' => $flash,
                                        'route' => 'admin.flashes.edit',
                                    ])
                                @endcan

                                @can('delete_flash')
                                    @include('core::includes.delete-icon-button', [
                                        'model' => $flash,
                                        'route' => 'admin.flashes.destroy',
                                    ])
                                @endcan
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 9])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $flashes->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>
@endsection
