@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php($items = [['title' => 'گزارش مشتریان به همراه سطح و امتیازات دریافتی']])
        <x-breadcrumb :items="$items" />
    </div>

    <x-card>
        <x-slot name="cardTitle">گزارش مشتریان به همراه سطح و امتیازات دریافتی</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <form id="filter-form" action="{{ route('admin.customersClub.getLevelUsers') }}" method="GET">
                <div class="row">

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label> انتخاب مشتری: </label>
                            <select name="customer_id" id="customer-selection" class="form-control search-customer-ajax">
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="from_date_show">از تاریخ :</label>
                            <input class="form-control fc-datepicker" id="from_date_show" type="text"
                                autocomplete="off" />
                            <input name="from_date" id="from_date_hide" type="hidden" value="{{ old('from_date') }}" />
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="to_date_show">تا تاریخ :</label>
                            <input class="form-control fc-datepicker" id="to_date_show" type="text" autocomplete="off" />
                            <input name="to_date" id="to_date_hide" type="hidden" value="{{ old('to_date') }}" />
                        </div>
                    </div>

                </div>

                <div class="row mb-3">

                    <div class="col-lg-8 col-md-6 col-12">
                        <button class="btn btn-primary btn-block" type="submit">جستجو <i class="fa fa-search"></i></button>
                    </div>

                    <div class="col-lg-4 col-md-6 col-12">
                        <a href="{{ route('admin.customersClub.getLevelUsers') }}" class="btn btn-danger btn-block">حذف همه
                            فیلتر ها <i class="fa fa-close"></i></a>
                    </div>

                </div>
            </form>
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        @php($tableTh = ['ردیف', 'کاربر', 'شماره موبایل', 'سطح', 'امتیاز', 'بن'])
                        @foreach ($tableTh as $th)
                            <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </x-slot>
                <x-slot name="tableTd">

                    @forelse ($users as $user)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $user->full_name ?? '-' }}</td>
                            <td>{{ $user->mobile }}</td>
                            <td>{{ $user->customers_club_level['level'] }}</td>
                            <td>{{ number_format($user->customers_club_score) }}</td>
                            <td>{{ $user->customers_club_bon }}</td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 6])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $users->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>
@endsection
@section('scripts')
    @include('core::includes.date-input-script', [
        'dateInputId' => 'from_date_hide',
        'textInputId' => 'from_date_show',
    ])

    @include('core::includes.date-input-script', [
        'dateInputId' => 'to_date_hide',
        'textInputId' => 'to_date_show',
    ])

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
