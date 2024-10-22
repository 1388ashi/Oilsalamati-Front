@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'لیست مشخصات']]" />
        @can('write_specification')
            <x-create-button route="admin.specifications.create" title="مشخصه جدید" />
        @endcan
    </div>

    <x-card>
        <x-slot name="cardTitle">جستجوی پیشرفته</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <form action="{{ route('admin.specifications.index') }}">

                <div class="row">

                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="form-group">
                            <label>نام :</label>
                            <input class="form-control" value="{{ request('name') }}" name="name" type="text">
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="form-group">
                            <label>لیبل :</label>
                            <input class="form-control" value="{{ request('label') }}" name="label" type="text">
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="form-group">
                            <label>گروه :</label>
                            <input class="form-control" value="{{ request('group') }}" name="group" type="text">
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="form-group">
                            <label>عمومی :</label>
                            <select name="public" class="form-control" id="is_public">
                                <option value="">همه</option>
                                <option value="1" {{ request('public') == '1' ? 'selected' : null }}>هست</option>
                                <option value="0" {{ request('public') == '0' ? 'selected' : null }}>نیست</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="form-group">
                            <label>الزامی :</label>
                            <select name="required" class="form-control" id="is_required">
                                <option value="">همه</option>
                                <option value="1" {{ request('required') == '1' ? 'selected' : null }}>باشد</option>
                                <option value="0" {{ request('required') == '0' ? 'selected' : null }}>نباشد
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="form-group">
                            <label>وضعیت :</label>
                            <select name="status" class="form-control" id="status">
                                <option value="">همه</option>
                                <option value="1" {{ request('status') == '1' ? 'selected' : null }}>فعال</option>
                                <option value="0" {{ request('status') == '0' ? 'selected' : null }}>غیر فعال</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="form-group">
                            <label>از تاریخ :</label>
                            <input class="form-control fc-datepicker" id="from_date_show" type="text" />
                            <input name="from_date" id="from_date_hide" type="hidden"
                                value="{{ request('from_date') }}" />
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3">
                        <div class="form-group">
                            <label>تا تاریخ :</label>
                            <input class="form-control fc-datepicker" id="to_date_show" type="text" />
                            <input name="to_date" id="to_date_hide" type="hidden" value="{{ request('to_date') }}" />
                        </div>
                    </div>

                </div>

                <div class="row">

                    <div class="col-12 col-md-6 col-xl-9">
                        <button class="btn btn-primary btn-block" type="submit">جستجو <i class="fa fa-search"></i></button>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3">
                        <a href="{{ route('admin.specifications.index') }}" class="btn btn-danger btn-block">حذف همه فیلتر
                            ها <i class="fa fa-close"></i></a>
                    </div>

                </div>

            </form>
        </x-slot>
    </x-card>

    <x-card>
        <x-slot name="cardTitle">لیست همه مشخصات ({{ $specifications->total() }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        <th>ردیف</th>
                        <th>نام (انگیلیسی)</th>
                        <th>لیبل (فارسی)</th>
                        <th>گروه</th>
                        <th>عمومی</th>
                        <th>الزامی</th>
                        <th>نمایش در فیلتر</th>
                        <th>وضعیت</th>
                        <th>تاریخ ثبت</th>
                        <th>عملیات</th>
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse ($specifications as $specification)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $specification->name }}</td>
                            <td>{{ $specification->label }}</td>
                            <td>{{ $specification->group }}</td>
                            <td>
                                @if ($specification->public)
                                    <span><i class="text-success fs-26 fa fa-check-circle-o"></i></span>
                                @else
                                    <span><i class="text-danger fs-24 fa fa-close"></i></span>
                                @endif
                            </td>
                            <td>
                                @if ($specification->required)
                                    <span><i class="text-success fs-26 fa fa-check-circle-o"></i></span>
                                @else
                                    <span><i class="text-danger fs-24 fa fa-close"></i></span>
                                @endif
                            </td>
                            <td>
                                @if ($specification->show_filter)
                                    <span><i class="text-success fs-26 fa fa-check-circle-o"></i></span>
                                @else
                                    <span><i class="text-danger fs-24 fa fa-close"></i></span>
                                @endif
                            </td>
                            <td>@include('core::includes.status', [
                                'status' => $specification->status,
                            ])</td>
                            <td>{{ verta($specification->created_at)->format('Y/m/d H:i') }}</td>
                            <td>

                                @can('modify_specification')
                                    @include('core::includes.edit-icon-button', [
                                        'model' => $specification,
                                        'route' => 'admin.specifications.edit',
                                    ])
                                @endcan

                                @can('delete_specification')
                                    @include('core::includes.delete-icon-button', [
                                        'model' => $specification,
                                        'route' => 'admin.specifications.destroy',
                                    ])
                                @endcan

                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 10])
                    @endforelse
                </x-slot>
                <x-slot
                    name="extraData">{{ $specifications->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>
@endsection
@section('scripts')
    @include('core::includes.date-input-script', [
        'textInputId' => 'from_date_show',
        'dateInputId' => 'from_date_hide',
    ])

    @include('core::includes.date-input-script', [
        'textInputId' => 'to_date_show',
        'dateInputId' => 'to_date_hide',
    ])

    <script>
        $('#is_required').select2({
            placeholder: 'الزامی بودن'
        });
        $('#is_public').select2({
            placeholder: 'عمومی بودن'
        });
        $('#status').select2({
            placeholder: 'انتخاب وضعیت'
        });
    </script>
@endsection
