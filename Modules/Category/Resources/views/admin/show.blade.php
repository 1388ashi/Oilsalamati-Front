@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php($items = [['title' => 'مرتب سازی دسته بندی']])
        <x-breadcrumb :items="$items" />
        <div>
            @if (!empty($sorts[0]))
                <button id="submitButton" type="submit" class="btn btn-teal align-items-center"><span>ذخیره مرتب سازی</span>
                    <i class="fe fe-code mr-1 font-weight-bold"></i>
                </button>
            @endif
            <x-create-button type="modal" target="createProductCategory" title="افزودن محصول به دسته بندی" />
        </div>
    </div>

    <!-- row opened -->
    <x-card>
        @if (!empty($sorts[0]))
            <x-slot name="cardTitle">مرتب سازی دسته بندی ({{ $sorts[0]->category_id }})</x-slot>
        @else
            <x-slot name="cardTitle">مرتب سازی دسته بندی</x-slot>
        @endif

        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            @if (!empty($sorts[0]))
                <form id="myForm" action="{{ route('admin.category-product-sort.update', $sorts[0]) }}" method="POST">
            @endif
            @csrf
            @method('PATCH')
            <div class="table-responsive">
                <table id="example-2" class="table table-striped table-bordered text-nowrap text-center">
                    <thead>
                        <tr>
                            <th class="border-top">انتخاب</th>
                            <th class="border-top">شناسه</th>
                            <th class="border-top">محصول</th>
                            <th class="border-top">حذف</th>
                        </tr>
                    </thead>
                    <tbody id="items">
                        @forelse($sorts as $sort)
                            <tr>
                                <td class="text-center"><i class="fe fe-move glyphicon-move text-dark"></i></td>
                                <input type="hidden" value="{{ $sort->id }}" name="orders[]">
                                <input type="hidden" value="{{ $sort->product_id }}" name="product_id">
                                <input type="hidden" value="{{ $sort->category_id }}" name="category_id">
                                <td>{{ $sort->product_id }}</td>
                                <td>{{ $sort->product_title }}</td>
                                <td>
                                    <button onclick="confirmDelete('delete-{{ $sort->id }}')"
                                        class="btn btn-sm btn-icon btn-danger text-white" data-toggle="tooltip"
                                        type="button" data-original-title="حذف" {{ isset($disabled) ? 'disabled' : null }}>
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
            @if (!empty($sorts[0]))
                <button class="btn btn-teal mt-5" type="submit">ذخیره مرتب سازی</button>
            @endif
            </form>
        </x-slot>
    </x-card>
    @include('category::admin.createProductCategory')
    <!-- row closed -->
    @foreach ($sorts as $sort)
        <form action="{{ route('admin.category-product-sort.destroy', $sort->id) }}" method="POST"
            id="delete-{{ $sort->id }}" style="display: none">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endsection
@section('scripts')
    <script>
        var items = document.getElementById('items');
        var sortable = Sortable.create(items, {
            handle: '.glyphicon-move',
            animation: 150
        });
        document.getElementById('submitButton').addEventListener('click', function() {
            document.getElementById('myForm').submit();
        });
    </script>
@endsection
