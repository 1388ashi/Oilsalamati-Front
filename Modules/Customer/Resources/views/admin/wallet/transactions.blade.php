@extends('admin.layouts.master')
@section('content')
    @php
        $transactionTypes = [
            'deposit' => [
                'title' => 'افزایش کیف پول',
                'bg-color' => 'success',
            ],
            'withdraw' => [
                'title' => 'برداشت از کیف پول',
                'bg-color' => 'danger',
            ],
        ];
    @endphp

    <div class="page-header">
        @php($items = [['title' => 'لیست تراکنش های کیف پول']])
        <x-breadcrumb :items="$items" />
    </div>

    <x-card>
        <x-slot name="cardTitle">جستجوی پیشرفته</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <form action="{{ route('admin.transactions.index') }}" method="GET">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <select class="form-control" name="type" id="type">
                                <option value="">انتخاب</option>
                                @foreach ($transactionTypes as $typeName => $attr)
                                    <option value="{{ $typeName }}"
                                        {{ request('type') == $typeName ? 'selected' : null }}>{{ $attr['title'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <select name="confirmed" class="form-control" id="confirmed">
                                <option value="">انتخاب</option>
                                <option value="1" {{ request('confirmed') == '1' ? 'selected' : null }}>فعال</option>
                                <option value="0" {{ request('confirmed') == '0' ? 'selected' : null }}>غیر فعال
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <input class="form-control fc-datepicker" id="start_date_show" type="text" autocomplete="off"
                                placeholder="از تاریخ" />
                            <input name="start_date" id="start_date_hide" type="hidden"
                                value="{{ request('start_date') }}" />
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <input class="form-control fc-datepicker" id="end_date_show" type="text" autocomplete="off"
                                placeholder="تا تاریخ" />
                            <input name="end_date" id="end_date_hide" type="hidden" value="{{ request('end_date') }}" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-9 col-lg-8 col-md-6 col-12">
                        <button class="btn btn-primary btn-block" type="submit">جستجو <i class="fa fa-search"></i></button>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                        <a href="{{ route('admin.transactions.index') }}" class="btn btn-danger btn-block">حذف همه فیلتر ها
                            <i class="fa fa-close"></i></a>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-card>

    <div class="row">
        <div class="col-12 col-xl-6">
            <div class="card bg-danger-transparent">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="mt-0 text-right">
                                <span class="fs-17 text-dark font-weight-bold"> جمع کل برداشت کیف پول این صفحه : </span>
                                <p class="mb-0 mt-1 text-danger font-weight-bold fs-16">
                                    {{ number_format(abs($totalThisPage['withdraw'])) }} تومان</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card bg-success-transparent">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="mt-0 text-right">
                                <span class="fs-17 text-dark font-weight-bold"> جمع کل افزایش کیف پول این صفحه : </span>
                                <p class="mb-0 mt-1 text-success font-weight-bold fs-16">
                                    {{ number_format(abs($totalThisPage['deposit'])) }} تومان</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-card>
        <x-slot name="cardTitle">تراکنش های کیف پول ({{ number_format($transactions->total()) }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        @php($tableTh = ['ردیف', 'شناسه', 'مبلغ (تومان)', 'مشتری', 'وضعیت', 'تاریخ ثبت', 'عملیات'])
                        @foreach ($tableTh as $th)
                            <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $transaction->id }}</td>
                            <td>{{ number_format(abs($transaction->amount)) }}</td>
                            <td>
                                <x-badge :type="$transactionTypes[$transaction->type]['bg-color']" :text="$transactionTypes[$transaction->type]['title']" />
                            </td>
                            <td>@include('core::includes.status', ['status' => $transaction->confirmed])</td>
                            <td>{{ $transaction->meta['description'] }}</td>
                            <td style="white-space: wrap">{{ $transaction->payable_id }}</td>
                            <td>{{ verta($transaction->created_at)->format('Y/m/d H:i') }}</td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 7])
                    @endforelse
                </x-slot>
                <x-slot
                    name="extraData">{{ $transactions->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>
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

    <script>
        $('#confirmed').select2({
            placeholder: 'انتخاب وضعیت'
        });
        $('#type').select2({
            placeholder: 'انتخاب نوع تراکنش'
        });
    </script>
@endsection
