@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'لیست مشتریان']]" />
        @can('write_customer')
            <x-create-button route="admin.customers.create" title="مشتری جدید" />
        @endcan
    </div>


    <x-card>
        <x-slot name="cardTitle">جستجوی پیشرفته</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <form action="{{ route('admin.customers.index') }}" method="GET">
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
                        <input class="form-control fc-datepicker" id="start_date_show" type="text" autocomplete="off"
                            placeholder="از تاریخ" />
                        <input name="start_date" id="start_date_hide" type="hidden" value="{{ old('start_date') }}" />
                    </div>
                    <div class="col-12 col-xl-3 form-group">
                        <input class="form-control fc-datepicker" id="end_date_show" type="text" autocomplete="off"
                            placeholder="تا تاریخ" />
                        <input name="end_date" id="end_date_hide" type="hidden" value="{{ old('end_date') }}" />
                    </div>
                    <div class="col-12 col-xl-3 form-group">
                        <label class="custom-control custom-checkbox mb-0">
                            <input type="checkbox" class="custom-control-input" name="has_transactions" value="1"
                                {{ request('has_transactions') == 1 ? 'checked' : null }} />
                            <span class="custom-control-label">دارای تراکنش کیف پول</span>
                        </label>
                    </div>
                    <div class="col-12 col-xl-3 form-group">
                        <label class="custom-control custom-checkbox mb-0">
                            <input type="checkbox" class="custom-control-input" name="has_deposits" value="1"
                                {{ request('has_deposits') == 1 ? 'checked' : null }} />
                            <span class="custom-control-label">شارژ کیف پول توسط مشتری</span>
                        </label>
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

    <x-card>
        <x-slot name="cardTitle">لیست مشتریان ({{ number_format($customers->total()) }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        @php($tableTh = ['ردیف', 'نام', 'نام خانوادگی', 'شناسه', 'شماره همراه', 'ایمیل', 'موجودی اصلی کیف پول (تومان)', 'تاریخ ثبت', 'عملیات'])
                        @foreach ($tableTh as $th)
                            <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse($customers as $customer)
                        <tr>
                            @php($mainBalance = $customer->wallet->balance - $customer->wallet->gift_balance)
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $customer->first_name ? $customer->first_name : '-' }}</td>
                            <td>{{ $customer->last_name ? $customer->last_name : '-' }}</td>
                            <td>{{ $customer->id }}</td>
                            <td>{{ $customer->mobile }}</td>
                            <td>{{ $customer->email ? $customer->email : '-' }}</td>
                            <td>{{ number_format($mainBalance) }}</td>
                            <td>{{ verta($customer->created_at)->format('Y/m/d H:i') }}</td>
                            <td>
                                @can('read_customer')
                                    @include('core::includes.show-icon-button', [
                                        'route' => 'admin.customers.show',
                                        'model' => $customer,
                                    ])
                                @endcan
                                @can('modify_customer')
                                    @include('core::includes.edit-icon-button', [
                                        'model' => $customer,
                                        'route' => 'admin.customers.edit',
                                    ])
                                @endcan
                                @can('delete_customer')
                                    @include('core::includes.delete-icon-button', [
                                        'model' => $customer,
                                        'route' => 'admin.customers.destroy',
                                        'disabled' => false,
                                    ])
                                @endcan
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 9])
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
