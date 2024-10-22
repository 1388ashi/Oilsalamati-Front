@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'لیست ویژگی ها']]" />
        @can('write_customer')
            <x-create-button route="admin.attributes.create" title="ویژگی جدید" />
        @endcan
    </div>

    <x-card>
        <x-slot name="cardTitle">جستجوی پیشرفته</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <form action="{{ route('admin.attributes.index') }}" method="GET">
                <div class="row">
                    <div class="col-xl-3 form-group">
                        <label>لیبل:</label>
                        <input type="text" name="label" value="{{ request('label') }}" class="form-control" />
                    </div>
                    <div class="col-xl-3 form-group">
                        <label for="code">نوع:</label>
                        <select class="form-control" name="type" id="type">
                            <option value="">همه</option>
                            @foreach ($types as $type)
                                <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : null }}>
                                    {{ config('attribute.types.' . $type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-3 form-group">
                        <div class="form-group">
                            <label>نمایش در فیلتر:</label>
                            <select name="show_filter" class="form-control" id="show-filter">
                                <option value="">همه</option>
                                <option value="0" @if (request('show_filter') == '0') selected @endif>نباشد</option>
                                <option value="1" @if (request('show_filter') == '1') selected @endif>باشد</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-3 form-group">
                        <div class="form-group">
                            <label>وضعیت :</label>
                            <select name="status" class="form-control" id="status">
                                <option value="">همه</option>
                                <option value="0" @if (request('status') == '0') selected @endif>غیر فعال</option>
                                <option value="1" @if (request('status') == '1') selected @endif>فعال</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-9">
                        <button class="col-12 btn btn-primary align-self-center">جستجو</button>
                    </div>
                    <div class="col-xl-3">
                        <a href="{{ route('admin.attributes.index') }}" class="col-12 btn btn-danger align-self-center">حذف
                            فیلتر ها<i class="fa fa-close" aria-hidden="true"></i></a>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-card>

    <x-card>
        <x-slot name="cardTitle">لیست همه ویژگی ها ({{ $attributes->total() }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        <th>ردیف</th>
                        <th>نام</th>
                        <th>لیبل</th>
                        <th>نوع</th>
                        <th>وضعیت</th>
                        <th>نمایش در فیلتر</th>
                        <th>تاریخ ثبت</th>
                        <th>عملیات</th>
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse($attributes as $attribute)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $attribute->name }}</td>
                            <td>{{ $attribute->label }}</td>
                            <td>{{ $attribute->type == 'select' ? 'انتخابی' : 'متنی' }}</td>
                            <td>
                                <x-badge isLight="true">
                                    <x-slot name="type">{{ $attribute->status ? 'success' : 'danger' }}</x-slot>
                                    <x-slot name="text">{{ $attribute->status ? 'فعال' : 'غیر فعال' }}</x-slot>
                                </x-badge>
                            </td>
                            <td>
                                @if ($attribute->show_filter)
                                    <span><i class="fs-26 fa fa-check-circle-o text-success"></i></span>
                                @else
                                    <span><i class="fs-26 fa fa-close text-danger"></i></span>
                                @endif
                            </td>
                            <td>{{ verta($attribute->created_at)->format('Y/m/d H:i') }}</td>
                            <td>
                                @include('core::includes.edit-icon-button', [
                                    'model' => $attribute,
                                    'route' => 'admin.attributes.edit',
                                ])
                                @include('core::includes.delete-icon-button', [
                                    'model' => $attribute,
                                    'route' => 'admin.attributes.destroy',
                                ])
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 8])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $attributes->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>
@endsection

@section('scripts')
    <script>
        $('#type').select2({
            placeholder: 'انتخاب نوع ویژگی'
        });
        $('#show-filter').select2({
            placeholder: 'نمایش در فیلتر'
        });
        $('#status').select2({
            placeholder: 'انتخاب وضعیت'
        });
    </script>
@endsection
