@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'مدیریت استوری اینستاگرام']]" />
        <x-create-button type="modal" target="createInstagramModal" title="تعیین بازه زمانی" />
    </div>
    <!-- row opened -->
    <x-card>
        <x-slot name="cardTitle">مدیریت استوری اینستاگرام</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <form id="myForm" action="{{ route('admin.customersClub.setStoryMention') }}" method="post">
                @csrf
                <div class="table-responsive">
                    <div id="hr-table-wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="control-label">انتخاب مشتری :<span
                                            class="text-danger">&starf;</span></label>
                                    <select class="form-control search-customer-ajax" id="customer-selection"
                                        name="   "></select>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary text-right item-right">تایید استوری کاربر</button>
                        </div>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-card>
    <x-modal id="createInstagramModal" size="md">
        <x-slot name="title">تعیین بازه زمانی</x-slot>
        <x-slot name="body">
            <form action="{{ route('admin.customersClub.setMinStoryHours') }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="control-label">بازه زمانی :<span class="text-danger">&starf;</span></label>
                                <input type="text" id="min_value" class="form-control" name="min_value"
                                    placeholder="بازه زمانی را وارد کنید" value="{{ old('min_value') }}" required
                                    autofocus />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button class="btn btn-primary  text-right item-right">ثبت</button>
                    <button class="btn btn-outline-danger  text-right item-right" data-dismiss="modal">برگشت</button>
                </div>
            </form>
        </x-slot>
    </x-modal>
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
