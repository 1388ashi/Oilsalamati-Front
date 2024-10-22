@extends('admin.layouts.master')

@section('content')
    <div class="page-header">
        @php($items = [['title' => 'مدیریت امتیازات خرید']])
        <x-breadcrumb :items="$items" />
        <div>
            <button id="submitButton" type="submit" class="btn btn-success align-items-center ml-2"><span>ثبت تغییرات</span>
                <x-create-button type="modal" target="createScoreModal" title="امتیاز جدید" />
        </div>
    </div>
    <!-- row opened -->
    <x-card>
        <x-slot name="cardTitle">مدیریت امتیازات خرید</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <div class="table-responsive">
                <table class="table table-striped table-bordered text-nowrap text-center">
                    <form id="myForm" action="{{ route('admin.customersClub.setPurchaseScore') }}" method="post">
                        @method('PUT')
                        @csrf
                        <thead>
                            <tr>
                                <th class="border-top">ردیف</th>
                                <th class="border-top">عنوان</th>
                                <th class="border-top">حداقل مبلغ خرید(تومان)</th>
                                <th class="border-top">حداکثر مبلغ خرید(تومان)</th>
                                <th class="border-top">مقدار بن</th>
                                <th class="border-top">مقدار امتیاز</th>
                                <th class="border-top">حذف</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse($list as $item)
                                <tr>
                                    <td class="font-weight-bold">{{ $loop->iteration }}</td>
                                    <input type="hidden" name="customers_club_purchase_scores[{{ $item->id }}][id]"
                                        value="{{ $item->id }}">
                                    <td><input type="text" class="form-control"
                                            name="customers_club_purchase_scores[{{ $item->id }}][title]"
                                            value="{{ $item->title }}"></td>
                                    <td><input type="text" class="form-control"
                                            name="customers_club_purchase_scores[{{ $item->id }}][min_value]"
                                            value="{{ number_format($item->min_value) }}"></td>
                                    <td><input type="text" class="form-control"
                                            name="customers_club_purchase_scores[{{ $item->id }}][max_value]"
                                            value="{{ number_format($item->max_value) }}"></td>
                                    <td><input type="text" class="form-control"
                                            name="customers_club_purchase_scores[{{ $item->id }}][bon_value]"
                                            value="{{ number_format($item->bon_value) }}"></td>
                                    <td><input type="text" class="form-control"
                                            name="customers_club_purchase_scores[{{ $item->id }}][score_value]"
                                            value="{{ number_format($item->score_value) }}"></td>
                                    <td>
                                        <button onclick="confirmDelete('delete-{{ $item->id }}')"
                                            class="btn btn-sm btn-icon btn-danger text-white" data-toggle="tooltip"
                                            type="button" data-original-title="حذف"
                                            {{ isset($disabled) ? 'disabled' : null }}>
                                            {{ isset($title) ? $title : null }}
                                            <i class="fa fa-trash-o {{ isset($title) ? 'mr-1' : null }}"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                @include('core::includes.data-not-found-alert', ['colspan' => 7])
                            @endforelse
                        </tbody>
                    </form>
                </table>
            </div>
        </x-slot>
    </x-card>
    @include('customersclub::admin.setting.createPurchaseScores')
    @foreach ($list as $item)
        <form action="{{ route('admin.customersClub.deletePurchaseScore', $item->id) }}" method="POST"
            id="delete-{{ $item->id }}" style="display: none">

            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endsection
@section('scripts')
    <script>
        document.getElementById('submitButton').addEventListener('click', function() {
            document.getElementById('myForm').submit();
        });
    </script>
@endsection
