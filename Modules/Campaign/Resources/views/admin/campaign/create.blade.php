@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php($items = [['title' => 'لیست کمپین ها', 'route_link' => 'admin.campaigns.index'], ['title' => 'ثبت کمپین جدید']])
        <x-breadcrumb :items="$items" />
    </div>

    <x-card>
        <x-slot name="cardTitle">ثبت کمپین جدید</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <form action="{{ route('admin.campaigns.store') }}" method="POST" class="save">
                @csrf
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="title" class="control-label"> عنوان : <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="title" class="form-control" name="title"
                                placeholder="عنوان را وارد کنید" value="{{ old('title') }}" required autofocus />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="	coupon_code" class="control-label">کد تخفیف :</label>
                            <input type="text" id="	coupon_code" class="form-control" name="coupon_code"
                                placeholder="عنوان را وارد کنید" value="{{ old('coupon_code') }}" autofocus />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="from_published_at_show" class="control-label">تاریخ شروع:<span
                                    class="text-danger">&starf;</span></label>
                            <input class="form-control fc-datepicker" id="from_published_at_show" type="text"
                                autocomplete="off" placeholder="تاریخ شروع را انتخاب کنید" />
                            <input name="start_date" id="from_published_at_hide" type="hidden"
                                value="{{ old('start_date') }}" />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="from_end_at_show" class="control-label">تاریخ پایان:<span
                                    class="text-danger">&starf;</span></label>
                            <input class="form-control fc-datepicker" id="from_end_at_show" type="text"
                                autocomplete="off" placeholder="تاریخ پایان را انتخاب کنید" />
                            <input name="end_date" id="from_end_at_hide" type="hidden" value="{{ old('end_date') }}" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="customer_title" class="control-label"> عنوان مشتری: <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="customer_title" class="form-control" name="customer_title"
                                placeholder="عنوان مشتری را وارد کنید" value="{{ old('customer_title') }}" required
                                autofocus />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="customer_text" class="control-label">توضیحات مشتری :<span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="customer_text" class="form-control" name="customer_text"
                                placeholder="توضیحات مشتری را وارد کنید" value="{{ old('customer_text') }}" autofocus />
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label for="label" class="control-label"> وضعیت : </label>
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="status" value="1"
                                {{ old('status', 1) == 1 ? 'checked' : null }} />
                            <span class="custom-control-label">فعال</span>
                        </label>
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
        'dateInputId' => 'from_published_at_hide',
        'textInputId' => 'from_published_at_show',
    ])
    @include('core::includes.date-input-script', [
        'dateInputId' => 'from_end_at_hide',
        'textInputId' => 'from_end_at_show',
    ])
@endsection
