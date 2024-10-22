@extends('admin.layouts.master')

@section('content')
    <div class="page-header">
        @php($items = [['title' => 'مدیریت امتیازات باشگاه']])
        <x-breadcrumb :items="$items" />
        <div>
            <button id="submitButton" type="submit" class="btn btn-success align-items-center">ثبت
                تغییرات</button>
        </div>
    </div>

    <x-card>
        <x-slot name="cardTitle">مدیریت امتیازات باشگاه</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <div class="table-responsive">
                <table class="table table-striped table-bordered text-nowrap text-center">
                    <form id="myForm" action="{{ route('admin.customersClub.setClubScores') }}" method="post">
                        @method('PUT')
                        @csrf
                        <thead>
                            <tr>
                                <th class="border-top">شناسه</th>
                                <th class="border-top">عنوان</th>
                                <th class="border-top">بن</th>
                                <th class="border-top">امتیاز</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse($list as $item)
                                <tr>
                                    <td class="font-weight-bold">{{ $item->id }}</td>
                                    <input type="hidden" name="customers_club_scores[{{ $item->id }}][id]"
                                        value="{{ $item->id }}">
                                    <td><input type="text" class="form-control"
                                            name="customers_club_scores[{{ $item->id }}][title]"
                                            value="{{ $item->title }}"></td>
                                    <td><input type="text" class="form-control"
                                            name="customers_club_scores[{{ $item->id }}][bon_value]"
                                            value="{{ $item->bon_value }}"></td>
                                    <td><input type="text" class="form-control"
                                            name="customers_club_scores[{{ $item->id }}][score_value]"
                                            value="{{ $item->score_value }}"></td>
                                </tr>
                            @empty
                                @include('core::includes.data-not-found-alert', ['colspan' => 4])
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
