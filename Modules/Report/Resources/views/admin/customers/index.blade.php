@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'گزارش مشتریان']]" />
    </div>


    <x-card>
        <x-slot name="cardTitle">جستجوی پیشرفته</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <form action="{{ route('admin.reports.customers') }}" method="GET">
                <div class="row">
                    <div class="col-12 col-xl-3 form-group">
                        <input type="text" name="id" placeholder="شناسه" value="{{ request('id') }}"
                            class="form-control" />
                    </div>
                    <div class="col-12 col-xl-3 form-group">
                        <input type="text" name="first_name" placeholder="نام" value="{{ request('first_name') }}"
                            class="form-control" />
                    </div>
                    <div class="col-12 col-xl-3 form-group">
                        <input type="text" name="last_name" placeholder="نام خانوادگی" value="{{ request('last_name') }}"
                            class="form-control" />
                    </div>
                    <div class="col-12 col-xl-3 form-group">
                        <input type="text" name="mobile" placeholder="شماره همراه" value="{{ request('mobile') }}"
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
                    <div class="col-xl-6">
                        <div style="display: flex;justify-content: start"><label> بازه ی مبلغ سفارش :</label></div>
                        @include('core::includes.between-range', [
                            'max' => $maxInvoiceAmount,
                            'id' => '1',
                            'name' => 'total',
                        ])
                    </div>

                    <div class="col-xl-6">
                        <div style="display: flex;justify-content: start"><label> بازه ی اقلام سفارش :</label></div>
                        @include('core::includes.between-range', [
                            'max' => $maxItemCount,
                            'id' => '2',
                            'name' => 'items_count',
                        ])
                    </div>
                </div>
                <div class="row" style="margin-top: 60px;">
                    <div class="col-9">
                        <button class="btn btn-primary btn-block">جستجو</button>
                    </div>
                    <div class="col-3">
                        <a href="{{ route('admin.reports.customers') }}" class="btn btn-danger btn-block">حذف فیلتر ها
                            <i class="fa fa-close" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-card>

    <x-card>
        <x-slot name="cardTitle">لیست مشتریان ({{ number_format($customers->total()) }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        <th>ردیف</th>
                        <th>شماره موبایل</th>
                        <th>تعداد سفارشات</th>
                        <th>شماره آخرین سفارش</th>
                        <th>تاریخ آخرین سفارش</th>
                        <th>مبلغ آخرین سفارش</th>
                        <th>ماه آخرین سفارش</th>
                        <th>مبلغ کل سفارشات</th>
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse($customers as $customer)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $customer->mobile }}</td>
                            <td>{{ $customer->orders_count }}</td>
                            @if ($customer->orders->isNotEmpty())
                                @php
                                    $lastOrder = $customer->orders->first();
                                @endphp
                                <td>{{ $lastOrder->id }}</td>
                                <td>{{ verta($lastOrder->created_at)->formatDate() }}</td>
                                <td>{{ number_format($lastOrder->total_invoices_amount) }}</td>
                                <td>{{ verta($lastOrder->created_at)->format('F') }}</td>
                                <td>{{ number_format($customer->orders->sum('total_invoices_amount')) }}</td>
                            @else
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                            @endif
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 8])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $customers->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
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
