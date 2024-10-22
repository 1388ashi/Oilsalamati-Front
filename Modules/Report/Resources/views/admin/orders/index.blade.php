@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'گزارش سفارشات']]" />
    </div>

    <x-card>
        <x-slot name="cardTitle">جستجوی پیشرفته</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <form action="{{ route('admin.reports.orders') }}" method="GET">
                <div class="row">
                    <div class="col-12 col-xl-3 form-group">
                        <input type="text" name="id" placeholder="شناسه سفارش" value="{{ request('id') }}"
                            class="form-control" />
                    </div>
                    <div class="col-12 col-xl-3 form-group">
                        <input type="text" name="customer_id" placeholder="شناسه مشتری"
                            value="{{ request('customer_id') }}" class="form-control" />
                    </div>
                    <div class="col-12 col-xl-3 form-group">
                        <input type="text" name="product_id" placeholder="شناسه محصول"
                            value="{{ request('product_id') }}" class="form-control" />
                    </div>
                    <div class="col-12 col-xl-3 form-group">
                        <input type="text" name="variety_id" placeholder="شناسه تنوع" value="{{ request('variety_id') }}"
                            class="form-control" />
                    </div>
                    <div class="col-12 col-xl-3 form-group">
                        <select id="ProvinceId" name="province_id" class="form-control">
                            <option value="">استان را انتخاب کنید</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-xl-3 form-group">
                        <select id="CityId" name="city_id" class="form-control">
                            <option value="">شهر را انتخاب کنید</option>
                        </select>
                    </div>
                    <div class="col-12 col-xl-3 form-group">
                        <input class="form-control fc-datepicker" id="start_date_show" type="text" autocomplete="off"
                            placeholder="از تاریخ" />
                        <input name="start_date" id="start_date_hide" type="hidden" value="{{ old('start_date') }}" />
                    </div>
                    <div class="col-12 col-xl-3 form-group">
                        <input class="form-control fc-datepicker" id="end_date_show" type="text" autocomplete="off"
                            placeholder="تا تاریخ" />
                        <input name="end_date" id="end_date_hide" type="hidden" value="{{ old('end_date') }}" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-9">
                        <button class="btn btn-primary btn-block">جستجو</button>
                    </div>
                    <div class="col-3">
                        <a href="{{ route('admin.reports.orders') }}" class="btn btn-danger btn-block">حذف فیلتر
                            ها
                            <i class="fa fa-close" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-card>

    <x-card>
        <x-slot name="cardTitle">لیست سفارشات ({{ number_format($orders->total()) }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        <th>ردیف</th>
                        <th>شناسه سفارش</th>
                        <th>تعداد اقلام</th>
                        <th>تخفیف (تومان)</th>
                        <th>هزینه حمل و نقل (تومان)</th>
                        <th>جمع کل (تومان)</th>
                        <th>تاریخ ثبت سفارش</th>
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse($orders as $order)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->items_count }}</td>
                            <td>{{ number_format($order->discount_amount) }}</td>
                            <td>{{ number_format($order->shipping_amount) }}</td>
                            <td>{{ number_format($order->total_amount) }}</td>
                            <td>{{ verta($order->created_at) }}</td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 7])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $orders->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>
@endsection

@section('scripts')
    <script>
        $(document).ready(() => {

            const provinces = @json($provinces);
            const provinceSelection = $('#ProvinceId');
            const citySelection = $('#CityId');

            provinceSelection.select2({
                placeholder: 'انتخاب استان'
            });
            citySelection.select2({
                placeholder: 'ابتدا استان را انتخاب کنید'
            });

            $('#ProvinceId').change(() => {

                let provinceId = $('#ProvinceId').val();
                let province = provinces.find(p => p.id == provinceId);
                let cities = province.cities;

                let option = '';
                cities.forEach((city) => {
                    option += `<option value="${city.id}">${city.name}</option>`;
                });

                citySelection.html(option);

            });

        });
    </script>

    @include('core::includes.date-input-script', [
        'dateInputId' => 'start_date_hide',
        'textInputId' => 'start_date_show',
    ])

    @include('core::includes.date-input-script', [
        'dateInputId' => 'end_date_hide',
        'textInputId' => 'end_date_show',
    ])
@endsection
