@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php
            $items = [
                ['title' => 'لیست حمل و نقل ها', 'route_link' => 'admin.shippings.index'],
                ['title' => 'مشاهده حمل و نقل'],
            ];
        @endphp
        <x-breadcrumb :items="$items" />
        <div class="d-flex align-items-center flex-wrap text-nowrap" style="gap: 8px;">
            @can('modify_shipping')
                @include('core::includes.edit-icon-button', [
                    'model' => $shipping,
                    'title' => 'ویرایش',
                    'route' => 'admin.shippings.edit',
                ])
            @endcan
            @can('delete_shipping')
                @include('core::includes.delete-icon-button', [
                    'model' => $shipping,
                    'title' => 'حذف',
                    'route' => 'admin.shippings.destroy',
                ])
            @endcan
            @can('write_shipping')
                <a href="{{ route('admin.shippings.create') }}" class="btn btn-indigo btn-sm btn-icon">
                    حمل و نقل جدید
                    <i class="fa fa-plus font-weight-bolder"></i>
                </a>
            @endcan
            @can('write_shipping')
                <a href="{{ route('admin.shipping-ranges.index', $shipping) }}" class="btn btn-rss btn-sm btn-icon">بازه ها
                    <i class="fa fa-align-justify"></i>
                </a>
            @endcan
        </div>
    </div>
    <x-card>
        <x-slot name="cardTitle">اطلاعات حمل و نقل</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <div class="row">
                <div class="col-lg-6">
                    <ul class="list-group">
                        <li class="list-group-item"><strong>کد: </strong> {{ $shipping->id }} </li>
                        <li class="list-group-item"><strong>عنوان: </strong> {{ $shipping->name }} </li>
                        <li class="list-group-item"><strong>قیمت پیش فرض: </strong>
                            {{ number_format($shipping->default_price) }} تومان</li>
                        <li class="list-group-item"><strong>حد ارسال رایگان: </strong>
                            {{ number_format($shipping->free_threshold) }} تومان</li>
                        <li class="list-group-item"><strong>سایز هر بسته: </strong> {{ $shipping->packet_size }} </li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <ul class="list-group">
                        <li class="list-group-item"><strong>سایز اولین بسته: </strong> {{ $shipping->first_packet_size }}
                        </li>
                        <li class="list-group-item"><strong>هزینه اضافه به ازای هر بسته: </strong>
                            {{ number_format($shipping->more_packet_price) }} تومان</li>
                        <li class="list-group-item"><strong>وضعیت: </strong>
                            <span class="{{ $shipping->status ? 'text-success' : 'text-danger' }}">
                                {{ $shipping->status ? 'فعال' : 'غیر فعال' }}</span>
                        </li>
                        <li class="list-group-item"><strong>تاریخ ثبت: </strong>
                            {{ verta($shipping->created_at)->format('Y/m/d H:i') }}</li>
                        <li class="list-group-item"><strong>نام ایجاد کننده: </strong> {{ $shipping->creator->name }}</li>
                    </ul>
                </div>
                @if ($shipping->description)
                    <div class="col-12 mt-4">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <strong class="d-block">توضیحات: </strong>
                                <p class="mt-2 fs-16">{{ $shipping->description }}</p>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>
        </x-slot>
    </x-card>
@endsection
