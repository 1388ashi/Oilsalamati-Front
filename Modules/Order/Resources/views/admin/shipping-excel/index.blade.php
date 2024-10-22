@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'ورود اکسل']]" />
    </div>

    <div class="row">
        <div class="col-xl-3">
            <x-card>
                <x-slot name="cardTitle">ورودی جدید</x-slot>
                <x-slot name="cardOptions"><x-card-options /></x-slot>
                <x-slot name="cardBody">
                    <form action="{{ route('admin.shipping-excels.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="form-group col-12">
                                <input type="file" class="form-control" name="file">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button class="btn btn-primary btn-block">آپلود</button>
                            </div>
                        </div>
                    </form>
                </x-slot>
            </x-card>
        </div>
        <div class="col-xl-9">
            <x-card>
                <x-slot name="cardTitle">جستجوی پیشرفته</x-slot>
                <x-slot name="cardOptions"><x-card-options /></x-slot>
                <x-slot name="cardBody">
                    <form action="{{ route('admin.shipping-excels.index') }}" method="GET">
                        <div class="row">
                            <div class="col-12 col-xl-3 form-group">
                                <input type="text" name="name" placeholder="گیرنده یا فرستنده"
                                    value="{{ request('name') }}" class="form-control" />
                            </div>
                            <div class="col-12 col-xl-3 form-group">
                                <input type="text" name="destination" placeholder="مقصد"
                                    value="{{ request('destination') }}" class="form-control" />
                            </div>
                            <div class="col-12 col-xl-3 form-group">
                                <input class="form-control fc-datepicker" id="start_date_show" type="text"
                                    autocomplete="off" placeholder="از تاریخ" />
                                <input name="start_date" id="start_date_hide" type="hidden"
                                    value="{{ request('start_date') }}" />
                            </div>
                            <div class="col-12 col-xl-3 form-group">
                                <input class="form-control fc-datepicker" id="end_date_show" type="text"
                                    autocomplete="off" placeholder="تا تاریخ" />
                                <input name="end_date" id="end_date_hide" type="hidden"
                                    value="{{ request('end_date') }}" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-9">
                                <button class="btn btn-primary btn-block">جستجو</button>
                            </div>
                            <div class="col-3">
                                <a href="{{ route('admin.customers.index') }}" class="btn btn-danger btn-block">حذف فیلتر ها
                                    <i class="fa fa-close" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </x-slot>
            </x-card>
        </div>
        <div class="col-12">
            <x-card>
                <x-slot name="cardTitle">اکسل پست</x-slot>
                <x-slot name="cardOptions"><x-card-options /></x-slot>
                <x-slot name="cardBody">
                    <div class="row mb-3">
                        <div class="col-12">
                            <button class="btn btn-outline-danger btn-sm">حذف گروهی</button>
                        </div>
                    </div>
                    <form action="{{ route('admin.shipping-excels.multiple-delete') }}" method="POST">

                        @csrf
                        @method('DELETE')

                        <x-table-component>
                            <x-slot name="tableTh">
                                <tr>
                                    <th>انتخاب</th>
                                    <th>ردیف</th>
                                    <th>بارکد</th>
                                    <th>نام گیرنده</th>
                                    <th>نام فرستنده</th>
                                    <th>مقصد</th>
                                    <th>هزینه کل (تومان)</th>
                                    <th>تاریخ ثبت</th>
                                    <th>عملیات</th>
                                </tr>
                            </x-slot>
                            <x-slot name="tableTd">
                                @forelse ($shippingExcels as $shippingExcel)
                                    <tr>
                                        <td class="font-weight-bold">{{ $loop->iteration }}</td>
                                        <td><input type="checkbox" name="ids[]" value="{{ $shippingExcel->id }}"></td>
                                        <td>{{ $shippingExcel->barcode }}</td>
                                        <td>{{ $shippingExcel->receiver_name }}</td>
                                        <td>{{ $shippingExcel->sender_name }}</td>
                                        <td>{{ $shippingExcel->destination }}</td>
                                        <td>{{ number_format($shippingExcel->price) }}</td>
                                        <td>{{ verta($shippingExcel->created_at)->format('Y/m/d H:i') }}</td>
                                        <td>
                                            @include('core::includes.delete-icon-button', [
                                                'model' => $shippingExcel,
                                                'route' => 'admin.shipping-excels.destroy',
                                            ])
                                        </td>
                                    </tr>
                                @empty
                                    @include('core::includes.data-not-found-alert', ['colspan' => 9])
                                @endforelse
                            </x-slot>
                            <x-slot
                                name="extraData">{{ $shippingExcels->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
                        </x-table-component>

                    </form>
                </x-slot>
            </x-card>
        </div>
    </div>
@endsection

@section('scripts')
    @include('core::includes.date-input-script', [
        'textInputId' => 'start_date_show',
        'dateInputId' => 'start_date_hide',
    ])
    @include('core::includes.date-input-script', [
        'textInputId' => 'end_date_show',
        'dateInputId' => 'end_date_hide',
    ])
@endsection
