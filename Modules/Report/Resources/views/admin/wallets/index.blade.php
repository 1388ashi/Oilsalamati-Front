@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'گزارش کیف پول']]" />
    </div>

    <x-card>
        <x-slot name="cardTitle">جستجوی پیشرفته</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <form action="{{ route('admin.reports.wallets') }}" method="GET">
                <div class="row">
                    <div class="col-12 col-xl-3 form-group">
                        <input type="text" name="id" placeholder="شناسه مشتری" value="{{ request('id') }}"
                            class="form-control" />
                    </div>
                    <div class="col-12 col-xl-3 form-group">
                        <input type="text" name="mobile" placeholder="موبایل مشتری" value="{{ request('mobile') }}"
                            class="form-control" />
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
                        <a href="{{ route('admin.reports.wallets') }}" class="btn btn-danger btn-block">حذف فیلتر ها
                            <i class="fa fa-close" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-card>

    <x-card>
        <x-slot name="cardTitle">گزارش کیف پول مشتریان ({{ number_format($customers->total()) }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        <th>ردیف</th>
                        <th>شناسه کاربر</th>
                        <th>نام</th>
                        <th>نام خانوادگی</th>
                        <th>موبایل</th>
                        <th>موجودی فعلی (تومان)</th>
                        <th>واریز (تومان)</th>
                        <th>برداشت (تومان)</th>
                        <th>دفعات واریز</th>
                        <th>دفعات برداشت</th>
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse($customers as $customer)
                        <tr>
                            @php($walletTransactions = $customer->all_wallet_transactions)
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $customer->id }}</td>
                            <td>{{ $customer->first_name ?? '-' }}</td>
                            <td>{{ $customer->last_name ?? '-' }}</td>
                            <td>{{ $customer->mobile }}</td>
                            <td>{{ number_format($walletTransactions['wallet_balance']) }}</td>
                            <td>{{ number_format($walletTransactions['total_deposit']) }}</td>
                            <td>{{ number_format($walletTransactions['total_withdraw']) }}</td>
                            <td>{{ $walletTransactions['deposit_count'] }}</td>
                            <td>{{ $walletTransactions['withdraw_count'] }}</td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 10])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $customers->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
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
@endsection
