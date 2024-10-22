@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php
            $items = [
                ['title' => 'لیست تخفیف ها', 'route_link' => 'admin.coupons.index'],
                ['title' => 'نمایش کد تخفیف', 'route_link' => null],
            ];
        @endphp
        <x-breadcrumb :items="$items" />
        <div style="display: flex; gap: 10px;">
            @can('modify_coupon')
                @include('core::includes.edit-icon-button', [
                    'model' => $coupon,
                    'route' => 'admin.coupons.edit',
                    'title' => 'ویرایش کد تخفیف',
                ])
            @endcan
            @can('delete_coupon')
                @include('core::includes.delete-icon-button', [
                    'model' => $coupon,
                    'route' => 'admin.coupons.destroy',
                    'title' => 'حذف کد تخفیف',
                ])
            @endcan
        </div>
    </div>

    <x-card>
        <x-slot name="cardTitle">اطلاعات کد تخفیف - کد {{ $coupon->id }}</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @php
                $amountType = [
                    'flat' => 'تومان',
                    'percentage' => 'درصد',
                ];
            @endphp
            <div class="row">
                <ul class="list-group">
                    <h4 class="my-3 mr-2"><strong>شناسه : </strong> {{ $coupon->id }} </h4>
                    <h4 class="my-3 mr-2"><strong>عنوان : </strong> {{ $coupon->title }} </h4>
                    <h4 class="my-3 mr-2"><strong>کد : </strong> {{ $coupon->code }} </h4>
                    <h4 class="my-3 mr-2"><strong>تاریخ شروع : </strong>
                        {{ verta($coupon->start_date)->format('Y/m/d H:i:s') }} </h4>
                    <h4 class="my-3 mr-2"><strong>تاریخ پایان : </strong>
                        {{ verta($coupon->end_date)->format('Y/m/d H:i:s') }} </h4>
                    <h4 class="my-3 mr-2"><strong>تاریخ پایان : </strong>
                        {{ verta($coupon->created_at)->format('Y/m/d H:i:s') }} </h4>
                    <h4 class="my-3 mr-2"><strong>نوع : </strong>
                        {{ $coupon->getQCouponTypeLabelAttribute($coupon->coupon_type) }} </h4>
                    <h4 class="my-3 mr-2"><strong>مقدار تخفیف : </strong>
                        {{ number_format($coupon->amount) . ' ' . $amountType[$coupon->type] }}</h4>
                    <h4 class="my-3 mr-2"><strong>سقف استفاده : </strong> {{ $coupon->usage_limit }} </h4>
                    <h4 class="my-3 mr-2"><strong>سقف استفاده برای هر کاربر : </strong> {{ $coupon->usage_per_user_limit }}
                    </h4>
                    <h4 class="my-3 mr-2"><strong>تعداد استفاده : </strong> {{ $coupon->customers()->count() }} </h4>
                    <h4 class="my-3 mr-2"><strong>حداقل مبلغ سبد خرید : </strong>
                        {{ number_format($coupon->min_order_amount) }} تومان</h4>
                </ul>
            </div>
        </x-slot>
    </x-card>
@endsection
