@extends('admin.layouts.master')
@section('styles')
    <style>
        .oneLine {
            position: relative;
        }

        .full-title {
            display: none;
            opacity: 0;
            visibility: hidden;
            position: absolute;
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 5px;
            z-index: 10;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            border-radius: 4px;
            width: 200px;
            white-space: normal;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }

        .oneLine:hover .full-title {
            display: block;
            opacity: 1;
            visibility: visible;
            transition-delay: 1s;
        }

        .short-title {
            display: inline;
        }
    </style>
@endsection
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'لیست محصولات']]" />
        @can('write_product')
            <x-create-button route="admin.products.create" title="محصول جدید" />
        @endcan
    </div>


    <form method="get" action="{{ route('admin.products.index') }}" autocomplete="off"
        onblur="document.form1.input.value = this.value;">
        <div class="row">
            <div class="col-xl-12 col-md-12 col-lg-12">
                <x-card>
                    <x-slot name="cardTitle">جستجوی پیشرفته</x-slot>
                    <x-slot name="cardOptions">
                        <x-card-options />
                    </x-slot>
                    <x-slot name="cardBody">
                        <div class="row">
                            <div class="col-12 col-xl-3 form-group">
                                <label for="name">شناسه</label>
                                <input class="form-control mb-4" placeholder="لطفا شناسه را وارد کنید" name="id"
                                    value="{{ request('id') }}" type="text">
                            </div>
                            <div class="col-12 col-xl-3 form-group">
                                <label for="name">عنوان</label>
                                <input class="form-control mb-4" placeholder="لطفا عنوان را وارد کنید" name="title"
                                    value="{{ request('title') }}" type="text">
                            </div>
                            <div class="col-12 col-xl-3 form-group">
                                <label>دسته بندی</label>
                                <select class="form-control custom-select select2 js-example-basic-multiple"
                                    name="category_id" data-placeholder="لطفا دسته بندی را انتخاب کنید ...">
                                    <option value=""></option>
                                    @foreach ($categories as $category)
                                        <option {{ request('category_id') == $category->id ? 'selected' : '' }}
                                            value="{{ $category->id }}">{{ $category->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-xl-3 form-group">
                                <label>وضعیت</label>
                                <select class="form-control select2" name="status">
                                    <option value="" selected>وضعیت را انتخاب کنید</option>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}"
                                            {{ request('status') == $status ? 'selected' : '' }}>
                                            {{ \Modules\Product\Entities\Product::getStatusLabelAttribute($status) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- <div class="col-12 col-xl-2 form-group">
                                <label>وضعیت تایید</label>
                                <select class="form-control select2" name="approved_at">
                                    <option value="">وضعیت تایید را انتخاب کنید</option>
                                    <option value="0" {{ request('approved_at') == '0' ? 'selected' : null }}>تایید
                                        نشده</option>
                                    <option value="1" {{ request('approved_at') == '1' ? 'selected' : null }}>تایید
                                        شده</option>

                                </select>
                            </div> --}}
                        </div>
                        <div class="row">
                            <div class="col-9">
                                <button class="btn btn-primary btn-block">جستجو <i class="fa fa-search"></i></button>
                            </div>
                            <div class="col-3">
                                <a href="{{ route('admin.products.index') }}" class="btn btn-danger btn-block">حذف فیلتر ها
                                    <i class="fa fa-close" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </x-slot>
                </x-card>
            </div>
        </div>
    </form>
    <!-- end advance Search-->

    <!-- row opened -->
    <x-card>
        <x-slot name="cardTitle">لیست همه محصولات ({{ $products->total() }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        <th class="border-top">ردیف</th>
                        <th class="border-top">شناسه</th>
                        <th class="border-top">عنوان</th>
                        <th class="border-top">قیمت</th>
                        <th class="border-top">قیمت همکار</th>
                        <th class="border-top">موجودی</th>
                        <th class="border-top">دسته بندی ها</th>
                        <th class="border-top">وضعیت</th>
                        <th class="border-top">وضعیت تایید</th>
                        <th class="border-top">تاریخ ثبت</th>
                        <th class="border-top">محصولات مشابه</th>
                        <th class="border-top">عملیات</th>
                    </tr>
                    </x-slot>
                    <x-slot name="tableTd">
                        @forelse($products as $product)
                            <tr>
                                <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                <td class="font-weight-bold">{{ $product->id }}</td>
                                <td class="oneLine">
                                    <span class="short-title">{{ Str::limit($product->title, 30) }}</span>
                                    @if (Str::length($product->title) > 30)
                                        <span class="full-title">{{ $product->title }}</span>
                                    @endif
                                </td>
                                <td>{{ number_format($product->getFinalPriceAttribute()['amount']) }}</td>
                                <td>{{ $product->getFinalPriceAttribute()['colleague_amount'] == 'none' ? 'ندارد' : number_format($product->getFinalPriceAttribute()['colleague_amount']) }}
                                </td>
                                <td>{{ $product->store_balance }}</td>
                                <td>{{ $product->categories->count() }}</td>
                                <td>@include('product::components.status', ['status' => $product->status])</td>
                                <td>
                                    @if ($product->approved_at == null)
                                        <form action="{{ route('admin.products.approve', $product->id) }}" method="post">
                                            @csrf

                                            <button class="btn btn-sm">
                                                <i class="text-danger fs-26 fa fa-close"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if ($product->approved_at != null)
                                        <form action="{{ route('admin.products.disapprove', $product->id) }}"
                                            method="post">
                                            @csrf
                                            <button class="btn btn-sm">
                                                <i class="text-success fs-26 fa fa-check-circle-o"></i>
                                            </button>
                                        </form>
                                    @endif

                                </td>
                                <td style="direction: ltr">{{ verta($product->created_at)->format('Y/m/d H:i') }}</td>
                                <td>
                                    <a class="btn btn-light btn-sm btn-icon text-white" data-toggle="tooltip"
                                        href="{{ route('admin.custom-related-product.index', $product->id) }}"
                                        data-original-title="محصولات مشابه"><i class="fa fa-list"></i></a>
                                </td>
                                <td class="text-center">
                                    @include('core::includes.edit-icon-button', [
                                        'model' => $product,
                                        'route' => 'admin.products.destroy',
                                    ])
                                    @include('core::includes.delete-icon-button', [
                                        'model' => $product,
                                        'route' => 'admin.products.destroy',
                                    ])
                                </td>
                            </tr>
                        @empty
                            @include('core::includes.data-not-found-alert', ['colspan' => 12])
                        @endforelse
                    </x-slot>
                    <x-slot name="extraData"><div class="mb-2">{{ $products->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}</div></x-slot>
            </x-table-component>
        </x-slot>
    </x-card>
@endsection
@section('scripts')
    <!--datetime pecker-->
    <script type="text/javascript">
        var $fromDate = new Date({{ request('from_date') }});
        var $toDate = new Date({{ request('to_date') }});

        $('#from_date_show').MdPersianDateTimePicker({
            targetDateSelector: '#from_date',
            targetTextSelector: '#from_date_show',
            englishNumber: false,
            fromDate: true,
            enableTimePicker: false,
            dateFormat: 'yyyy-MM-dd',
            textFormat: 'yyyy-MM-dd',
            groupId: 'rangeSelector1',
        });

        $('#to_date_show').MdPersianDateTimePicker({
            targetDateSelector: '#to_date',
            targetTextSelector: '#to_date_show',
            englishNumber: false,
            toDate: true,
            enableTimePicker: false,
            dateFormat: 'yyyy-MM-dd',
            textFormat: 'yyyy-MM-dd',
            groupId: 'rangeSelector1',
        });
    </script>
@endsection
