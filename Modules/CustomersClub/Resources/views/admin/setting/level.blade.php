@extends('admin.layouts.master')

@section('content')
    <div class="page-header">
        @php($items = [['title' => 'مدیریت سطح مشتریان']])
        <x-breadcrumb :items="$items" />
        <div>
            <button id="submitButton" type="submit" class="btn btn-success align-items-center"><span>ثبت
                    تغییرات</span></button>
        </div>
    </div>

    <x-card>
        <x-slot name="cardTitle">مدیریت سطح مشتریان</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <div class="table-responsive">
                <table class="table table-striped table-bordered text-nowrap text-center">
                    <form id="myForm" action="{{ route('admin.customersClub.setUserLevels') }}" method="post">
                        @method('PUT')
                        @csrf
                        <thead>
                            <tr>
                                <th class="border-top">شناسه</th>
                                <th class="border-top">عنوان</th>
                                <th class="border-top">حداقل امتیاز</th>
                                <th class="border-top">حداکثر امتیاز</th>
                                <th class="border-top">رنگ</th>
                                <th class="border-top">تخفیف خرید دائمی(تومان)</th>
                                <th class="border-top">تخفیف تولد(تومان)</th>
                                <th class="border-top">ارسال رایگان(تومان)</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse($list as $item)
                                <tr>
                                    <td class="font-weight-bold">{{ $item->id }}</td>
                                    <input type="hidden" name="customers_club_user_levels[{{ $item->id }}][id]"
                                        value="{{ $item->id }}">
                                    <td><input type="text" class="form-control"
                                            name="customers_club_user_levels[{{ $item->id }}][title]"
                                            value="{{ $item->title }}"></td>
                                    <td><input type="text" class="form-control"
                                            name="customers_club_user_levels[{{ $item->id }}][min_score]"
                                            value="{{ $item->min_score }}"></td>
                                    <td><input type="text" class="form-control"
                                            name="customers_club_user_levels[{{ $item->id }}][max_score]"
                                            value="{{ $item->max_score }}"></td>
                                    <td><input type="text" class="form-control"
                                            name="customers_club_user_levels[{{ $item->id }}][color]"
                                            value="{{ $item->color }}"></td>
                                    <td><input type="text" class="form-control"
                                            name="customers_club_user_levels[{{ $item->id }}][permanent_purchase_discount]"
                                            value="{{ number_format($item->permanent_purchase_discount) }}"></td>
                                    <td><input type="text" class="form-control"
                                            name="customers_club_user_levels[{{ $item->id }}][birthdate_discount]"
                                            value="{{ number_format($item->birthdate_discount) }}"></td>
                                    <td><input type="text" class="form-control"
                                            name="customers_club_user_levels[{{ $item->id }}][free_shipping]"
                                            value="{{ number_format($item->free_shipping) }}"></td>
                                </tr>
                            @empty
                                @include('core::includes.data-not-found-alert', ['colspan' => 8])
                            @endforelse
                        </tbody>
                    </form>
                </table>
            </div>
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
