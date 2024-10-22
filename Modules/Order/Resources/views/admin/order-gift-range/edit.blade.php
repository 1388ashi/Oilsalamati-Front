@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php
            $items = [
                ['title' => 'لیست هدایا', 'route_link' => 'admin.order-gift-ranges.index'],
                ['title' => 'ویرایش هدیه'],
            ];
        @endphp
        <x-breadcrumb :items="$items" />
    </div>
    @include('components.errors')

    <x-card>
        <x-slot name="cardTitle">ویرایش هدیه کد - {{ $orderGiftRange->id }}</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <form action="{{ route('admin.order-gift-ranges.update', $orderGiftRange) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="title" class="control-label"> عنوان: <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="title" class="form-control" name="title"
                                value="{{ old('title', $orderGiftRange->title) }}" />
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="image" class="control-label">تصویر هدیه:<label>
                                    <input type="file" id="image" class="form-control" name="image"
                                        value="{{ old('image') }}">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="price" class="control-label"> قیمت هدیه (تومان): <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="price" class="form-control comma" name="price"
                                value="{{ old('price', number_format($orderGiftRange->price)) }}" />
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="min_order_amount" class="control-label"> پایه مبلغ سفارش (تومان): <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="min_order_amount" class="form-control comma" name="min_order_amount"
                                value="{{ old('min_order_amount', number_format($orderGiftRange->min_order_amount)) }}" />
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="description" class="control-label">توضیحات هدیه : <span
                                    class="text-danger">&starf;</span></label>
                            <textarea class="form-control" name="description" id="description" rows="3">{{ old('description', $orderGiftRange->description) }}</textarea>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="text-center">
                            <button class="btn btn-warning" type="submit">بروزرسانی</button>
                        </div>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-card>
@endsection
