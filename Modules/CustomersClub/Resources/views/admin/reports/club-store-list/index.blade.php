@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php($items = [['title' => 'گزارش امتیازات کسب شده']])
        <x-breadcrumb :items="$items" />
    </div>
    <x-card>
        <x-slot name="cardTitle">امتیازات کسب شده توسط کاربر</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <form action="{{ route('admin.customersClub.getClubScoreList') }}" method="GET">
                <div class="row">

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label> انتخاب مشتری: </label>
                            <select name="customer_id" id="customer-selection" class="form-control search-customer-ajax">
                            </select>
                        </div>
                    </div>

                </div>

                <div class="row mb-3">
                    <div class="col-lg-4">
                        <button class="btn btn-primary btn-block" type="submit">جستجو <i class="fa fa-search"></i></button>
                    </div>
                </div>
            </form>
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        @php($tableTh = ['ردیف', 'امتیاز', 'بن', 'عنوان', 'محصول', 'تاریخ'])
                        @foreach ($tableTh as $th)
                            <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse ($scores as $score)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $score->score_value }}</td>
                            <td>{{ $score->bon_value }}</td>
                            <td>{{ $score->cause_title }}</td>
                            <td>{{ $score->product ?: '-' }}</td>
                            <td>{{ verta($score->created_at)->format('Y/m/d H:i') }}</td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 6])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">{{ $scores->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</x-slot>
            </x-table-component>
        </x-slot>
    </x-card>
@endsection
@section('scripts')
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
