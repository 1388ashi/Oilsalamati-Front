@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php($items = [['title' => 'مدیریت نظرسنجی اینماد']])
        <x-breadcrumb :items="$items" />
    </div>
    <!-- row opened -->
    <x-card>
        <x-slot name="cardTitle">مدیریت نظرسنجی اینماد</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <form id="myForm" action="{{ route('admin.customersClub.setEnamadScore') }}" method="post">
                @csrf
                <div class="table-responsive">
                    <div id="hr-table-wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="control-label">انتخاب مشتری :<span
                                            class="text-danger">&starf;</span></label>
                                    <select class="form-control search-customer-ajax" id="customer-selection"
                                        name="customer_id"></select>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary text-right item-right">تایید نظرسنجی اینماد</button>
                        </div>
                    </div>
                </div>
            </form>
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
