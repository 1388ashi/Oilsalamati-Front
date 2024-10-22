@extends('admin.layouts.master')

@section('content')
    <div class="page-header">
        @php($items = [['title' => 'لیست محصولات پیشنهادی', 'route_link' => 'admin.recommendations.index'], ['title' => 'لیست آیتم های محصول پیشنهادی']])
        <x-breadcrumb :items="$items" />
        <div>
            <button id="submitButton" type="submit" class="btn btn-teal submit-disable align-items-center"
                style="display: none"><span>ذخیره مرتب سازی</span><i class="fe fe-code mr-1 font-weight-bold"></i></button>
            <button class="btn btn-primary create-disable" data-target="#createItemsModal" data-toggle="modal">
                ثبت آیتم جدید
                <i class="fa fa-plus mr-1"></i>
            </button>
        </div>
    </div>

    <x-card>
        <x-slot name="cardTitle">لیست آیتم های گروه {{ $recommendation->group_name }} -
            {{ $recommendation->title }}</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <form action="{{ route('admin.recommendation-items.sort', $recommendation) }}" id="myForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="table-responsive">
                    <table class="table table-striped table-bordered text-nowrap text-center">
                        <thead>
                            <tr>
                                <th class="border-top">انتخاب</th>
                                <th class="border-top">محصول</th>
                                <th class="border-top">تاریخ ثبت</th>
                                <th class="border-top">عملیات</th>
                            </tr>
                        </thead>
                        <tbody class="text-center" id="items">
                            @forelse($recommendationItems as $recommendationItem)
                                <tr>
                                    <td class="text-center"><i class="fe fe-move glyphicon-move text-dark"></i></td>
                                    <input type="hidden" value="{{ $recommendationItem->id }}"
                                        name="recommendation_items[]">
                                    <td>{{ $recommendationItem->product->title }}</td>
                                    <td>{{ verta($recommendationItem->created_at)->format('Y/m/d H:i') }}
                                    </td>
                                    <td>
                                        <button onclick="confirmDelete('delete-{{ $recommendationItem->id }}')"
                                            class="btn btn-sm btn-icon btn-danger text-white delete-disable"
                                            data-toggle="tooltip" type="button" data-original-title="حذف"
                                            {{ isset($disabled) ? 'disabled' : null }}>
                                            {{ isset($title) ? $title : null }}
                                            <i class="fa fa-trash-o {{ isset($title) ? 'mr-1' : null }}"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                @include('core::includes.data-not-found-alert', ['colspan' => 4])
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </x-slot>
    </x-card>
    @foreach ($recommendationItems as $recommendationItem)
        <form action="{{ route('admin.recommendation-items.destroy', [$recommendation, $recommendationItem->id]) }}"
            method="POST" id="delete-{{ $recommendationItem->id }}" style="display: none">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
    @include('product::admin.recommendation-items.create')
    <!-- row closed -->
@endsection
@section('scripts')
    <script>
        var items = document.getElementById('items');
        var disableButtons = false;

        var sortable = Sortable.create(items, {
            handle: '.glyphicon-move',
            animation: 150,
            onEnd: function(event) {
                disableButtons = true;
                if (disableButtons) {
                    $('.create-disable').prop('disabled', true);
                    $('.delete-disable').prop('disabled', true);
                    $('.submit-disable').show();
                    console.log('Buttons disabled');
                } else {
                    $('.create-disable').prop('disabled', false);
                    $('.delete-disable').prop('disabled', false);

                    console.log('Buttons enabled');
                }
            }
        });

        document.getElementById('submitButton').addEventListener('click', function() {
            document.getElementById('myForm').submit();
        });
    </script>
@endsection
