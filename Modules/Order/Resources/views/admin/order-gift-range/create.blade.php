@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php
            $items = [
                ['title' => 'لیست هدایا', 'route_link' => 'admin.order-gift-ranges.index'],
                ['title' => 'ثبت هدیه جدید'],
            ];
        @endphp
        <x-breadcrumb :items="$items" />
    </div>

    @include('components.errors')

    <x-card>
        <x-slot name="cardTitle">ثبت هدیه جدید</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <form action="{{ route('admin.order-gift-ranges.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="title" class="control-label"> عنوان: <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="title" class="form-control" name="title"
                                value="{{ old('title') }}" />
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="image" class="control-label"> تصویر هدیه: <span
                                    class="text-danger">&starf;</span></label>
                            <input type="file" id="image" class="form-control" name="image"
                                value="{{ old('image') }}">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="price" class="control-label"> قیمت هدیه (تومان): <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="price" class="form-control comma" name="price"
                                value="{{ old('price') }}" />
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="min_order_amount" class="control-label"> پایه مبلغ سفارش (تومان): <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="min_order_amount" class="form-control comma" name="min_order_amount"
                                value="{{ old('min_order_amount') }}" />
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="description" class="control-label">توضیحات هدیه : <span
                                    class="text-danger">&starf;</span></label>
                            <textarea class="form-control" name="description" id="description" rows="3">{{ old('description') }}</textarea>
                        </div>
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
