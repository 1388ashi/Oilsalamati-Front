@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php
            $items = [
                ['title' => 'لیست تخفیف ها', 'route_link' => 'admin.coupons.index'],
                ['title' => 'ثبت تخفیف جدید', 'route_link' => null],
            ];
        @endphp
        <x-breadcrumb :items="$items" />
    </div>

    <x-card>
        <x-slot name="cardTitle">ثبت تخفیف جدید</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-alert-danger />
            <form action="{{ route('admin.coupons.store') }}" method="POST">
                @csrf
                <div class="row">

                    <div class="col-12 col-xl-4">
                        <div class="form-group">
                            <label for="title" class="control-label"> عنوان: <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="title" class="form-control" name="title"
                                value="{{ old('title') }}" required autofocus />
                        </div>
                    </div>

                    <div class="col-12 col-xl-4">
                        <div class="form-group">
                            <label for="code" class="control-label"> کد: <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="code" class="form-control" name="code"
                                value="{{ old('code') }}" required autofocus />
                        </div>
                    </div>

                    <div class="col-12 col-xl-4">
                        <div class="form-group">
                            <label for="start_date_show" class="control-label">تاریخ شروع : <span
                                    class="text-danger">&starf;</span></label>
                            <input class="form-control fc-datepicker" id="start_date_show" type="text" autocomplete="off"
                                placeholder="تاریخ شروع را انتخاب کنید" />
                            <input name="start_date" id="start_date_hide" type="hidden" value="{{ old('start_date') }}" />
                        </div>
                    </div>

                    <div class="col-12 col-xl-4">
                        <div class="form-group">
                            <label for="end_date_show" class="control-label">تاریخ پایان : <span
                                    class="text-danger">&starf;</span></label>
                            <input class="form-control fc-datepicker" id="end_date_show" type="text" autocomplete="off"
                                placeholder="تاریخ پایان را انتخاب کنید" />
                            <input name="end_date" id="end_date_hide" type="hidden" value="{{ old('end_date') }}" />
                        </div>
                    </div>

                    <div class="col-12 col-xl-4">
                        <div class="form-group">
                            <label for="type" class="control-label"> نوع: </label>
                            <select class="form-control" name="type" id="type">
                                <option value="flat" {{ old('type') == 'flat' ? 'selected' : '' }}>
                                    مبلغ
                                </option>
                                <option value="percentage" {{ old('type') == 'percentage' ? 'selected' : '' }}>
                                    درصد
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-xl-4">
                        <div class="form-group">
                            <label for="amount" id="amount-label" class="control-label"></label>
                            <input type="text" id="amount" class="form-control comma" name="amount"
                                value="{{ old('amount') }}" />
                        </div>
                    </div>

                    <div class="col-12 col-xl-4">
                        <div class="form-group">
                            <label for="usage_limit" class="control-label"> سقف استفاده : <span
                                    class="text-danger">&starf;</span></label>
                            <input type="number" id="usage_limit" class="form-control" name="usage_limit"
                                value="{{ old('usage_limit') }}" />
                        </div>
                    </div>

                    <div class="col-12 col-xl-4">
                        <div class="form-group">
                            <label for="usage_per_user_limit" class="control-label"> سقف استفاده برای هر کاربر : <span
                                    class="text-danger">&starf;</span></label>
                            <input type="number" id="usage_per_user_limit" class="form-control" name="usage_per_user_limit"
                                value="{{ old('usage_per_user_limit') }}" />
                        </div>
                    </div>

                    <div class="col-12 col-xl-4">
                        <div class="form-group">
                            <label for="min_order_amount" class="control-label"> حداقل مبلغ سبد خرید (تومان) : <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="min_order_amount" class="form-control comma"
                                name="min_order_amount" value="{{ old('min_order_amount') }}" />
                        </div>
                    </div>

                </div>

                <div class="row">
                    <div class="col">
                        <div class="text-center">
                            <button class="btn btn-primary" type="submit">ثبت و ذخیره</button>
                        </div>
                    </div>
                </div>

            </form>
        </x-slot>
    </x-card>
@endsection

@section('scripts')
    @include('core::includes.date-input-script', [
        'dateInputId' => 'end_date_hide',
        'textInputId' => 'end_date_show',
    ])

    @include('core::includes.date-input-script', [
        'dateInputId' => 'start_date_hide',
        'textInputId' => 'start_date_show',
    ])

    <script>
        $(document).ready(function() {

            let initialValue = $('#type').val();
            let text = initialValue === 'flat' ? 'مبلغ : ' : 'درصد : ';
            $('#amount-label').text(text).append('<span class="text-danger">&starf;</span>');

            $('#type').on('change', () => {
                text = $('#type').val() === 'flat' ? 'مبلغ : ' : 'درصد : ';
                $('#amount-label').text(text).append('<span class="text-danger">&starf;</span>');
            });

        });
    </script>
@endsection
