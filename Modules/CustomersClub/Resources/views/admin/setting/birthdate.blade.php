@extends('admin.layouts.master')

@section('content')
    <div class="page-header">
        @php($items = [['title' => 'مدیریت تنظیمات تخفیف تولد']])
        <x-breadcrumb :items="$items" />
        <div>
            <button id="submitButton" type="submit" class="btn btn-success align-items-center"><span>ثبت
                    تغییرات</span>
        </div>
    </div>

    <x-card>
        <x-slot name="cardTitle">مدیریت تنظیمات تخفیف تولد</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <form id="myForm" action="{{ route('admin.customersClub.setBirthdateSettings') }}" method="post">
                @csrf
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="control-label">تعداد روز اعتبار تخفیف :</label>
                            <input type="number" id="days_birth_date_discount_active" class="form-control"
                                name="days_birth_date_discount_active"
                                value="{{ $birth_date_settings['days_birth_date_discount_active'] }}" required autofocus />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="control-label">تعداد مجاز برای استفاده از تخفیف :</label>
                            <input type="number" id="max_birth_date_discount_usage" class="form-control"
                                name="max_birth_date_discount_usage"
                                value="{{ $birth_date_settings['max_birth_date_discount_usage'] }}" required autofocus />
                        </div>
                    </div>
                </div>
                </div>
            </form>
        </x-slot>
    </x-card>
@endsection
@section('scripts')
    <script>
        document.getElementById('submitButton').addEventListener('click', function() {
            document.getElementById('myForm').submit();
        });
    </script>
@endsection
