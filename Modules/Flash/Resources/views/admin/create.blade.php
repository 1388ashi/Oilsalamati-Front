@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php
            $items = [
                ['title' => 'لیست کمپین های فروش', 'route_link' => 'admin.flashes.index'],
                ['title' => 'ثبت کمپین فروش جدید'],
            ];
        @endphp
        <x-breadcrumb :items="$items" />
    </div>

    <x-card>
        <x-slot name="cardTitle">ثبت کمپین فروش جدید</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <form action="{{ route('admin.flashes.store') }}" method="POST" class="save" enctype="multipart/form-data">
                @csrf
                <div class="row">

                    <div class="col-12">
                        <div class="form-group">
                            <label for="title" class="control-label"> عنوان: <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="title" class="form-control" name="title"
                                placeholder="عنوان را وارد کنید" value="{{ old('title') }}" required autofocus />
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="start_date_show" class="control-label">تاریخ شروع : <span
                                    class="text-danger">&starf;</span></label>
                            <input class="form-control fc-datepicker" id="start_date_show" type="text" autocomplete="off"
                                placeholder="تاریخ شروع را انتخاب کنید" />
                            <input name="start_date" id="start_date_hide" type="hidden" value="{{ old('start_date') }}" />
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="end_date_show" class="control-label">تاریخ پایان : <span
                                    class="text-danger">&starf;</span></label>
                            <input class="form-control fc-datepicker" id="end_date_show" type="text" autocomplete="off"
                                placeholder="تاریخ پایان را انتخاب کنید" />
                            <input name="end_date" id="end_date_hide" type="hidden" value="{{ old('end_date') }}" />
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
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

                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="amount" id="amount-label" class="control-label"></label>
                            <input type="text" id="amount" class="form-control comma" name="amount"
                                value="{{ old('amount') }}" />
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="image" class="control-label"> تصویر: <span
                                    class="text-danger">&starf;</span></label>
                            <input type="file" id="image" class="form-control" name="image"
                                value="{{ old('image') }}">
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="mobile_image" class="control-label"> تصویر موبایل: <span
                                    class="text-danger">&starf;</span></label>
                            <input type="file" id="mobile_image" class="form-control" name="mobile_image"
                                value="{{ old('mobile_image') }}">
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="status" value="1"
                                    {{ old('status', 1) == 1 ? 'checked' : null }} />
                                <span class="custom-control-label">فعال</span>
                            </label>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label for="products" class="control-label"> محصولات: <span
                                    class="text-danger">&starf;</span></label>
                            <label class="custom-control custom-checkbox">
                                <input id="all-products" type="checkbox" value="false" onclick="toggleCheckBox()"
                                    class="custom-control-input" />
                                <span class="custom-control-label">همه محصولات</span>
                            </label>
                            <select class="form-control select2" multiple id="products">
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}"
                                        {{ old('products') == $product->id ? 'selected' : null }}>
                                        {{ $product->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="products-discount-section" class="col-md-8 mx-auto table-responsive mt-4">
                        <table id="products-discount-table" role="table"
                            class="table b-table table-hover table-bordered text-center border-top">
                            <thead role="rowgroup">
                                <tr role="row">
                                    <th role="columnheader" scope="col" aria-colindex="1">شناسه</th>
                                    <th role="columnheader" scope="col" aria-colindex="2">عنوان</th>
                                    <th role="columnheader" scope="col" aria-colindex="3">نوع</th>
                                    <th role="columnheader" scope="col" aria-colindex="4">تخفیف (تومان)</th>
                                    <th role="columnheader" scope="col" aria-colindex="5">عملیات</th>
                                </tr>
                            </thead>
                            <tbody role="rowgroup">

                            </tbody>
                        </table>
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
        $(document).ready(() => {

            let initialValue = $('#type').val();
            let text = initialValue === 'flat' ? 'مبلغ : ' : 'درصد : ';
            $('#amount-label').text(text).append('<span class="text-danger">&starf;</span>');

            $('#type').on('change', () => {
                text = $('#type').val() === 'flat' ? 'مبلغ : ' : 'درصد : ';
                $('#amount-label').text(text).append('<span class="text-danger">&starf;</span>');
            });


            $('#products-discount-section').hide();
            let counter = 0;

            $('#products').on('change', function() {
                const selectedProducts = $(this).val();

                if (!selectedProducts || selectedProducts.length === 0) {
                    $('#products-discount-section').hide();
                    $('#products-discount-table tbody').empty();
                    return;
                }

                $('#products-discount-section').show();
                $('#products-discount-table tbody').empty();

                selectedProducts.forEach((productId) => {
                    const product = {!! json_encode($products) !!}.find(c => c.id == productId);
                    if (product) {
                        $('#products-discount-table tbody').append(`  
                  <tr role="row">  
                    <td role="cell" aria-colindex="1" class="product-id">${product.id}</td>  
                    <td role="cell" aria-colindex="2" class="product-title">${product.title}</td>  
                    <td role="cell" aria-colindex="3" class="product-discount_type">  
                      <select class="form-control" name="products[${counter}][discount_type]">  
                        <option value="percentage">درصد</option>  
                        <option value="flat">مبلغ</option>  
                      </select>  
                    </td>  
                    <td role="cell" aria-colindex="4" class="product-discount">  
                      <input type="text" class="form-control comma" name="products[${counter}][discount]">  
                      <input type="hidden" class="form-control" name="products[${counter}][id]" value="${product.id}">
                    </td>  
                    <td role="cell" aria-colindex="5">  
                      <button type="button" class="delete-btn btn btn-sm btn-icon btn-danger text-white">  
                        <i class="fa fa-trash-o"></i>  
                      </button>  
                    </td>  
                  </tr>  
                `);
                        counter++;
                    }
                });

                comma();
            });

            $('#all-products').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#products-discount-section').show();
                    $('#products-discount-table tbody').empty();

                    const products = {!! json_encode($products) !!};

                    products.forEach((product) => {
                        $('#products-discount-table tbody').append(`  
                  <tr role="row">  
                    <td role="cell" aria-colindex="1" class="product-id">${product.id}</td>  
                    <td role="cell" aria-colindex="2" class="product-title">${product.title}</td>  
                    <td role="cell" aria-colindex="3" class="product-discount_type">  
                      <select class="form-control" name="products[${counter}][discount_type]">  
                        <option value="percentage">درصد</option>  
                        <option value="flat">مبلغ</option>  
                      </select>  
                    </td>  
                    <td role="cell" aria-colindex="4" class="product-discount">  
                      <input type="number" class="form-control comma" name="products[${counter}][discount]">  
                      <input type="hidden" class="form-control" name="products[${counter}][id]" value="${product.id}">  
                    </td>  
                    <td role="cell" aria-colindex="5">  
                      <button type="button" class="delete-btn btn btn-sm btn-icon btn-danger text-white">  
                        <i class="fa fa-trash-o"></i>  
                      </button>  
                    </td>  
                  </tr>  
                `);
                        counter++;
                    });
                } else {
                    $('#products-discount-section').hide();
                    $('#products-discount-table tbody').empty();
                }

                comma();
            });

            $('#products-discount-table').on('click', '.delete-btn', function() {
                $(this).closest('tr').remove();
            });

        });
    </script>
@endsection
