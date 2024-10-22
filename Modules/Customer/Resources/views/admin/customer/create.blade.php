@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php
            $items = [
                ['title' => 'لیست مشتریان', 'route_link' => 'admin.customers.index'],
                ['title' => 'ثبت مشتری جدید'],
            ];
        @endphp
        <x-breadcrumb :items="$items" />
    </div>

    <x-card>
        <x-slot name="cardTitle">ثبت مشتری جدید</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <form action="{{ route('admin.customers.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-12 col-xl-3 col-lg-6 form-group">
                        <label for="first_name" class="control-label"> نام: <span class="text-danger">&starf;</span></label>
                        <input type="text" id="first_name" class="form-control" name="first_name"
                            value="{{ old('first_name') }}" />
                    </div>
                    <div class="col-12 col-xl-3 col-lg-6 form-group">
                        <label for="last_name" class="control-label">نام خانوادگی: <span
                                class="text-danger">&starf;</span></label>
                        <input type="text" id="last_name" class="form-control" name="last_name"
                            value="{{ old('last_name') }}" />
                    </div>
                    <div class="col-12 col-xl-3 col-lg-6 form-group">
                        <label for="mobile" class="control-label">شماره همراه: <span
                                class="text-danger">&starf;</span></label>
                        <input type="text" id="mobile" class="form-control" name="mobile"
                            value="{{ old('mobile') }}" />
                    </div>
                    <div class="col-12 col-xl-3 col-lg-6 form-group">
                        <label for="password" class="control-label"> کلمه عبور: <span
                                class="text-danger">&starf;</span></label>
                        <input type="text" id="password" class="form-control" name="password"
                            value="{{ old('password') }}" />
                    </div>
                    <div class="col-12 col-xl-3 col-lg-6 form-group">
                        <label for="email" class="control-label"> ایمیل:</label>
                        <input type="text" id="email" class="form-control" name="email"
                            value="{{ old('email') }}" />
                    </div>
                    <div class="col-12 col-xl-3 col-lg-6 form-group">
                        <label for="national_code" class="control-label"> کد ملی:</label>
                        <input type="text" id="national_code" class="form-control" name="national_code"
                            value="{{ old('national_code') }}" />
                    </div>
                    <div class="col-12 col-xl-3 col-lg-6 form-group">
                        <label for="card_number" class="control-label"> شماره کارت:</label>
                        <input type="text" id="card_number" class="form-control" name="card_number"
                            value="{{ old('card_number') }}" />
                    </div>
                    <div class="col-12 col-xl-3 col-lg-6 form-group">
                        <label for="from_published_at_show" class="control-label">تاریخ تولد :</label>
                        <input class="form-control fc-datepicker" id="from_published_at_show" type="text"
                            autocomplete="off" />
                        <input name="birth_date" id="from_published_at_hide" type="hidden"
                            value="{{ old('birth_date') }}" />
                    </div>
                    <div class="col-12 form-group">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="newsletter" value="1"
                                {{ old('newsletter', 1) == 1 ? '' : null }} />
                            <span class="custom-control-label">خبرنامه</span>
                        </label>
                    </div>
                    <div class="col-12 form-group">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="foreign_national" value="1"
                                {{ old('foreign_national', 1) == 1 ? '' : null }} />
                            <span class="custom-control-label">تبعه خارجی</span>
                        </label>
                    </div>
                    <div class="col-12 form-group">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="status" value="1"
                                {{ old('status', 1) == 1 ? 'checked' : null }} />
                            <span class="custom-control-label">وضعیت</span>
                        </label>
                    </div>
                    <div class="col-12">
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
@endsection
