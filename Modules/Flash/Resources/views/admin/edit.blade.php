@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php
            $items = [
                ['title' => 'لیست کمپین های فروش', 'route_link' => 'admin.flashes.index'],
                ['title' => 'ویرایش کمپین فروش'],
            ];
        @endphp
        <x-breadcrumb :items="$items" />
    </div>

    <x-card>
        <x-slot name="cardTitle">ویرایش کمپین فروش - کد {{ $flash->id }}</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <form action="{{ route('admin.flashes.update', $flash) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">

                    <div class="col-12">
                        <div class="form-group">
                            <label for="title" class="control-label"> عنوان: <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="title" class="form-control" name="title"
                                placeholder="عنوان را وارد کنید" value="{{ old('title', $flash->title) }}" required
                                autofocus />
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="start_date_show" class="control-label">تاریخ شروع : <span
                                    class="text-danger">&starf;</span></label>
                            <input class="form-control fc-datepicker" id="start_date_show" type="text" autocomplete="off"
                                placeholder="تاریخ شروع را انتخاب کنید" />
                            <input name="start_date" id="start_date_hide" type="hidden"
                                value="{{ old('start_date', $flash->start_date) }}" />
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="end_date_show" class="control-label">تاریخ پایان : <span
                                    class="text-danger">&starf;</span></label>
                            <input class="form-control fc-datepicker" id="end_date_show" type="text" autocomplete="off"
                                placeholder="تاریخ پایان را انتخاب کنید" />
                            <input name="end_date" id="end_date_hide" type="hidden"
                                value="{{ old('end_date', $flash->end_date) }}" />
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="type" class="control-label"> نوع: </label>
                            <select class="form-control" name="type" id="type">
                                <option value="flat" {{ old('type', $flash->type) == 'flat' ? 'selected' : '' }}>
                                    مبلغ
                                </option>
                                <option value="percentage"
                                    {{ old('type', $flash->type) == 'percentage' ? 'selected' : '' }}>
                                    درصد
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="amount" id="amount-label" class="control-label"></label>
                            <input type="text" id="amount" class="form-control comma" name="amount"
                                value="{{ old('amount', number_format($flash->amount)) }}" />
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
                                    {{ old('status', $flash->status) == 1 ? 'checked' : null }} />
                                <span class="custom-control-label">فعال</span>
                            </label>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label for="products" class="control-label"> محصولات: <span
                                    class="text-danger">&starf;</span></label>
                            <select class="form-control select2" multiple id="products">
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}"
                                        {{ $flash->products->contains($product->id) ? 'selected' : null }}>
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
                                @foreach ($flash->products as $product)
                                    <tr role="row">
                                        <td role="cell" aria-colindex="1" class="product-id">{{ $product->id }}</td>
                                        <td role="cell" aria-colindex="2" class="product-title">{{ $product->title }}
                                        </td>
                                        <td role="cell" aria-colindex="3" class="product-discount_type">
                                            <select class="form-control"
                                                name="products[{{ $loop->index }}][discount_type]">
                                                <option value="percentage">درصد</option>
                                                <option value="flat">مبلغ</option>
                                            </select>
                                        </td>
                                        <td role="cell" aria-colindex="4" class="product-discount">
                                            <input type="number" class="form-control"
                                                name="products[{{ $loop->index }}][discount]"
                                                value="{{ $product->pivot->discount }}">
                                        </td>
                                        <td role="cell" aria-colindex="4" class="product-discount"
                                            style="display: none">
                                            <input type="text" class="form-control"
                                                name="products[{{ $loop->index }}][id]" value="{{ $product->id }}">
                                        </td>
                                        <td role="cell" aria-colindex="5">
                                            <button type="button"
                                                class="delete-btn btn btn-sm btn-icon btn-danger text-white"
                                                data-del = "{{ $product->id }}">
                                                <i class="fa fa-trash-o"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
                <div class="row">
                    <div class="col">
                        <div class="text-center">
                            <button class="btn btn-warning" type="submit">بروزرسانی</button>
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
        let arr = {}

        @foreach ($flash->products as $p)
            arr[{{ $p->id }}] = {{ $p->pivot->discount }}
        @endforeach
        $(document).ready(() => {

            let initialValue = $('#type').val();
            let text = initialValue === 'flat' ? 'مبلغ : ' : 'درصد : ';
            $('#amount-label').text(text).append('<span class="text-danger">&starf;</span>');

            $('#type').on('change', () => {
                text = $('#type').val() === 'flat' ? 'مبلغ : ' : 'درصد : ';
                $('#amount-label').text(text).append('<span class="text-danger">&starf;</span>');
            });

            if (!@json($flash->products).length != 0) {
                $('#products-discount-section').hide();
            }

            let counter = 1000;

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
                        <input type="number" class="form-control comma" name="products[${counter}][discount]" value="${arr[product.id]}">
                      </td>
                       <td role="cell" aria-colindex="4" class="product-discount" style="display: none">
                          <input type="text" class="form-control" name="products[${counter}][id]" value="${product.id}">
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
            });

            $('#products-discount-table').on('click', '.delete-btn', function() {
                const deleteId = this.dataset.del
                let selectInput = $("#products").val()
                selectInput = selectInput.filter(item => item != deleteId)

                $("#products").val(selectInput).trigger('change.select2')
                $(this).closest('tr').remove();
            });

        });
    </script>
@endsection
