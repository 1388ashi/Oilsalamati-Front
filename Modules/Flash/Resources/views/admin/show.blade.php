@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php
            $items = [
                ['title' => 'کمپین های فروش', 'route_link' => 'admin.flashes.index'],
                ['title' => 'نمایش جزئیات کمپین', 'route_link' => null],
            ];
        @endphp
        <x-breadcrumb :items="$items" />
        <div style="display: flex; gap: 10px;">
            @can('modify_flash')
                @include('core::includes.edit-icon-button', [
                    'model' => $flash,
                    'route' => 'admin.flashes.edit',
                    'title' => 'ویرایش',
                ])
            @endcan
            @can('delete_flash')
                @include('core::includes.delete-icon-button', [
                    'model' => $flash,
                    'route' => 'admin.flashes.destroy',
                    'title' => 'حذف',
                ])
            @endcan
        </div>
    </div>

    <x-card>
        <x-slot name="cardTitle">جزئیات کمپین</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <div class="row">
                @php
                    $flashDetails = [
                        ['key' => 'شناسه', 'value' => $flash->id],
                        ['key' => 'عنوان', 'value' => $flash->title],
                        ['key' => 'میزان بازدید', 'value' => $flash->preview_count],
                        ['key' => 'تاریخ شروع', 'value' => verta($flash->birth_date)],
                        ['key' => 'تاریخ پایان', 'value' => verta($flash->end_date)],
                        ['key' => 'تاریخ ثبت', 'value' => verta($flash->created_at)],
                        ['key' => 'وضعیت', 'value' => $flash->status ? 'فعال' : 'غیر فعال'],
                    ];
                @endphp
                @foreach ($flashDetails as $detail)
                    <div class="col-12 col-xl-4 col-lg-6 my-1">
                        <strong> {{ $detail['key'] }} : </strong>
                        <span> {{ $detail['value'] }} </span>
                    </div>
                @endforeach
            </div>
        </x-slot>
    </x-card>

    <x-card>
        <x-slot name="cardTitle">محصولات درون کمپین ({{ $flash->products->count() }})</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        <th>ردیف</th>
                        <th>عنوان</th>
                        <th>شناسه محصول</th>
                        <th>قیمت خام (تومان)</th>
                        <th>نوع تخفیف</th>
                        <th>مقدار تخفیف</th>
                        <th>قیمت نهایی (تومان)</th>
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse ($flash->products as $product)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td>{{ $product->title }}</td>
                            <td>{{ $product->id }}</td>
                            <td>{{ number_format($product->minor_variety_price) }}</td>
                            <td>
                                @php
                                    $discountTypes = [
                                        'percentage' => 'درصد',
                                        'flat' => 'تومان',
                                    ];
                                @endphp
                                {{ $discountTypes[$product->pivot->discount_type] }}
                            </td>
                            <td>{{ $product->pivot->discount }}</td>
                            <td>
                                @if ($product->pivot->discount_type == 'percentage')
                                    {{ number_format($product->minor_variety_price - ($product->minor_variety_price * $product->pivot->discount) / 100) }}
                                @else
                                    {{ number_format($product->minor_variety_price - $product->pivot->discount) }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 7])
                    @endforelse
                </x-slot>
            </x-table-component>
        </x-slot>
    </x-card>
@endsection
