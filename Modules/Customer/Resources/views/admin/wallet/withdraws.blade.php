@extends('admin.layouts.master')
@section('content')
    @php
        $statuses = [
            'pending' => [
                'title' => 'در انتظار تکمیل',
                'class' => 'badge badge-info-light',
            ],
            'paid' => [
                'title' => 'پرداخت شده',
                'class' => 'badge badge-success-light',
            ],
            'canceled' => [
                'title' => 'لغو شده',
                'class' => 'badge badge-danger-light',
            ],
        ];
    @endphp

    <div class="page-header">
        @php($items = [['title' => 'برداشت های کیف پول', 'route_link' => null]])
        <x-breadcrumb :items="$items" />
    </div>

    <x-card>
        <x-slot name="cardTitle">جستجوی پیشرفته</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <form action="{{ route('admin.withdraws.index') }}" method="GET">
                <div class="row">
                    <div class="col-xl-3 col-lg-6 col-12">
                        <div class="form-group">
                            <select name="customer_id" id="customer-selection" class="form-control search-customer-ajax">
                                @if (request('customer_id'))
                                    <option selected>{{ request('customer_id') }}</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-12">
                        <div class="form-group">
                            <select name="status" id="status-selection" class="form-control">
                                <option value="">انتخاب</option>
                                @foreach ($statuses as $name => $attr)
                                    <option value="{{ $name }}"
                                        {{ request('status') == $name ? 'selected' : null }}>{{ $attr['title'] }} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-12">
                        <div class="form-group">
                            <input class="form-control fc-datepicker" id="start_date_show" type="text" autocomplete="off"
                                placeholder="از تاریخ" />
                            <input name="start_date" id="start_date_hide" type="hidden"
                                value="{{ request('start_date') }}" />
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-12">
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
                        <a href="{{ route('admin.withdraws.index') }}" class="btn btn-danger btn-block">حذف همه فیلتر ها <i
                                class="fa fa-close"></i></a>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-card>

    <x-card>
        <x-slot name="cardTitle">برداشت های کیف پول ({{ number_format($withdrawsCount) }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        <th>ردیف</th>
                        <th>شناسه</th>
                        <th>مبلغ (تومان)</th>
                        <th>مشتری</th>
                        <th>وضعیت</th>
                        <th>تاریخ</th>
                        <th>عملیات</th>
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse ($withdraws as $withdraw)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $withdraw->id }}</td>
                            <td>{{ number_format(abs($withdraw->amount)) }}</td>
                            <td>{{ $withdraw->customer->mobile }}</td>
                            <td>
                                <span class="{{ $statuses[$withdraw->status]['class'] }}">
                                    {{ config('customer.withdraw_statuses.' . $withdraw->status) }}</span>
                            </td>
                            <td>{{ verta($withdraw->created_at)->format('Y/m/d H:i') }}</td>
                            <td>
                                <button class="btn btn-sm btn-icon btn-warning text-white"
                                    data-target="#edit-withdraw-modal-{{ $withdraw->id }}" data-toggle="modal"
                                    type="button" data-original-title="ویرایش">
                                    <i class="fa fa-pencil"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 7])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $withdraws->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>

    @include('customer::admin.includes.edit-withdraw-modal')
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
        $('#status-selection').select2({
            placeholder: 'انتخاب وضعیت'
        });
    </script>

    <script>
        $('.search-customer-ajax').select2({
            ajax: {
                url: '{{ route('admin.customers.search') }}',
                dataType: 'json',
                processResults: (response) => {
                    let customers = response.data.customers || [];

                    return {
                        results: customers.map(customer => ({
                            id: customer.id,
                            mobile: customer.mobile,
                        })),
                    };
                },
                cache: true,
            },
            placeholder: 'جستجوی مشتری',
            templateResult: (repo) => {

                if (repo.loading) {
                    return "در حال بارگذاری...";
                }

                let $container = $(
                    "<div class='select2-result-repository clearfix'>" +
                    "<div class='select2-result-repository__meta'>" +
                    "<div class='select2-result-repository__title'></div>" +
                    "</div>" +
                    "</div>"
                );

                let text = `شناسه: ${repo.id} | موبایل: ${repo.mobile}`;
                $container.find(".select2-result-repository__title").text(text);
                return $container;
            },
            minimumInputLength: 1,
            templateSelection: (repo) => {
                return repo.mobile ? `موبایل: ${repo.mobile}` : repo.text;
            }
        });
    </script>
@endsection
