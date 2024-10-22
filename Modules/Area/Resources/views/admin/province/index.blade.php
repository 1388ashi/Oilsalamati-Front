@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'لیست استان ها']]" />
        @can('write_area')
            <x-create-button type="modal" target="createProvinceModal" title="استان جدید" />
        @endcan
    </div>

    <x-card>
        <x-slot name="cardTitle">جستجوی پیشرفته</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <div class="row">
                <form action="{{ route('admin.provinces.index') }}" method="GET" class="col-12">
                    <div class="row">

                        <div class="col-lg-3">
                            <div class="form-group">
                                <input type="text" class="form-control" name="name" value="{{ request('name') }}"
                                    placeholder="عنوان">
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="form-group">
                                <input type="number" class="form-control" name="id" value="{{ request('id') }}"
                                    placeholder="شناسه">
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="form-group">
                                <input class="form-control fc-datepicker" id="start_date_show" type="text"
                                    autocomplete="off" placeholder="از تاریخ" />
                                <input name="start_date" id="start_date_hide" type="hidden"
                                    value="{{ request('start_date') }}" />
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="form-group">
                                <input class="form-control fc-datepicker" id="end_date_show" type="text"
                                    autocomplete="off" placeholder="تا تاریخ" />
                                <input name="end_date" id="end_date_hide" type="hidden" value="{{ request('end_date') }}" />
                            </div>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-xl-9 col-lg-8 col-md-6 col-12">
                            <button class="btn btn-primary btn-block" type="submit">جستجو <i
                                    class="fa fa-search"></i></button>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                            <a href="{{ route('admin.provinces.index') }}" class="btn btn-danger btn-block">حذف همه فیلتر ها
                                <i class="fa fa-close"></i></a>
                        </div>

                    </div>
                </form>
            </div>
        </x-slot>
    </x-card>

    <x-card>
        <x-slot name="cardTitle">استان ها ({{ $provinces->total() }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        @php($tableTh = ['ردیف', 'نام', 'تعداد شهر ها', 'وضعیت', 'تاریخ ثبت', 'عملیات'])
                        @foreach ($tableTh as $th)
                            <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse ($provinces as $province)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $province->name }}</td>
                            <td>{{ $province->cities->count() }}</td>
                            <td>@include('core::includes.status', ['status' => $province->status])</td>
                            <td>{{ verta($province->created_at)->format('Y/m/d H:i') }}</td>
                            <td>
                                @include('core::includes.show-icon-button ', [
                                    'model' => $province,
                                    'route' => 'admin.provinces.show',
                                ])
                                @can('modify_area')
                                    @include('core::includes.edit-modal-button', [
                                        'target' => '#editProvinceModal-' . $province->id,
                                    ])
                                @endcan
                                @can('delete_area')
                                    @include('core::includes.delete-icon-button', [
                                        'model' => $province,
                                        'route' => 'admin.provinces.destroy',
                                        'disabled' => $province->cities->isNotEmpty(),
                                    ])
                                @endcan
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 6])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $provinces->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>

    @can('write_area')
        @include('area::admin.province.includes.create-modal')
    @endcan

    @can('modify_area')
        @include('area::admin.province.includes.edit-modal')
    @endcan
@endsection

@section('scripts')
    @include('core::includes.date-input-script', [
        'dateInputId' => 'start_date_hide',
        'textInputId' => 'start_date_show',
    ])

    @include('core::includes.date-input-script', [
        'dateInputId' => 'end_date_hide',
        'textInputId' => 'end_date_show',
    ])
@endsection
