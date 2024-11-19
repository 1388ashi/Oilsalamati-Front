@extends('front.layouts.master')
@section('body_class') template-product product-layout1 @endsection
@section('content')
<div class="page-header mt-0 py-3">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                <div class="breadcrumbs">
                    <a href="/" title="Back to the home page">صفحه اصلی</a>
                    <span class="main-title fw-bold"><i class="icon anm anm-angle-left-l"></i>سفارش موفقیت</span>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="page-header text-center">
    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12 d-flex justify-content-between align-items-center">
            <div class="page-title"><h1>سفارش موفقیت</h1></div>
                <div class="breadcrumbs">
                    <a href="index.html" title="Back to the home page">صفحه اصلی</a>
                    <span class="main-title"><i class="icon anm anm-angle-left-l"></i>دستور موفقیت</span>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container">
    <!--Order success-->
    <div class="success-text checkout-card text-center mb-4 mb-md-5">
        <i class="icon anm anm-shield-check"></i>
        <h2>از سفارش شما متشکریم!</h2>
        <p class="mb-1">پرداخت با موفقیت انجام شد و سفارش شما در راه است</p>
        <p class="mb-1">
            یک ایمیل تأیید سفارش با جزئیات سفارش و پیوندی برای پیگیری پیشرفت
            آن دریافت خواهید کرد.
        </p>
        <p class="text-order badge bg-success mt-3">
            شناسه تراکنش: {{$order->id}}
        </p>
    </div>
    <!--End Order success-->

    <div class="row checkout-form">
        <div class="col-12 col-sm-12 col-md-12 col-lg-6">
            <!--Order Summary-->
            <div class="block order-summary">
                <div class="block-content">
                    <h3 class="title mb-3">خلاصه سفارش</h3>
                    <div class="table-responsive-sm table-bottom-brd order-table">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-start">تصویر</th>
                                <th class="text-start proName">محصول</th>
                                <th class="text-center">تعداد</th>
                                <th class="text-center">قیمت</th>
                                <th class="text-center">مجموع فرعی</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="text-start">
                            <a href="product-layout1.html" class="thumb"
                                ><img
                                class="rounded-0 blur-up lazyload"
                                data-src="assets/images/products/product1-120x170.jpg"
                                src="assets/images/products/product1-120x170.jpg"
                                alt="محصول"
                                title="محصول"
                                width="120"
                                height="170"
                            /></a>
                            </td>
                            <td class="text-start proName">
                            <div class="list-view-item-title">
                                <a href="product-layout1.html"
                                >پیراهن کوبایی آکسفورد</a
                                >
                            </div>
                            <div class="cart-meta-text">
                                رنگ: مشکی<br />سایز: کوچک
                            </div>
                            </td>
                            <td class="text-center">2</td>
                            <td class="text-center">99 هزار</td>
                            <td class="text-center"><strong>198 هزار</strong></td>
                        </tr>
                        <tr>
                            <td class="text-start">
                            <a href="product-layout1.html" class="thumb"
                                ><img
                                class="rounded-0 blur-up lazyload"
                                data-src="assets/images/products/product2-120x170.jpg"
                                src="assets/images/products/product2-120x170.jpg"
                                alt="محصول"
                                title="محصول"
                                width="120"
                                height="170"
                            /></a>
                            </td>
                            <td class="text-start proName">
                            <div class="list-view-item-title">
                                <a href="product-layout1.html">کلاه کاف</a>
                            </div>
                            <div class="cart-meta-text">
                                رنگ: مشکی<br />سایز: کوچک
                            </div>
                            </td>
                            <td class="text-center">1</td>
                            <td class="text-center">128 هزار</td>
                            <td class="text-center"><strong>128 هزار</strong></td>
                        </tr>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
            <div class="cart-info mt-4 mb-4 mb-lg-0">
                <div class="cart-order-detail cart-col">
                    <div class="row g-0 border-bottom pb-2">
                        <span class="col-6 col-sm-6 cart-subtotal-title">
                            <strong>زیر مجموع</strong>
                        </span>
                        <span class="col-6 col-sm-6 cart-subtotal-title cart-subtotal text-end">
                            <span class="money">326 هزار</span>
                        </span>
                    </div>
                    <div class="row g-0 border-bottom py-2">
                        <span class="col-6 col-sm-6 cart-subtotal-title">
                            <strong>تخفیف کوپن</strong>
                        </span>
                        <span class="col-6 col-sm-6 cart-subtotal-title cart-subtotal text-end">
                            <span class="money">-25 هزار</span>
                        </span>
                    </div>
                    <div class="row g-0 border-bottom py-2">
                        <span class="col-6 col-sm-6 cart-subtotal-title">
                            <strong>مالیات</strong>
                        </span>
                        <span class="col-6 col-sm-6 cart-subtotal-title cart-subtotal text-end">
                            <span class="money">10 هزار</span>
                        </span>
                    </div>
                    <div class="row g-0 border-bottom py-2">
                        <span class="col-6 col-sm-6 cart-subtotal-title">
                            <strong>ارسال</strong>
                        </span>
                        <span class="col-6 col-sm-6 cart-subtotal-title cart-subtotal text-end"
                            ><span class="money">ارسال رایگان</span>
                        </span>
                    </div>
                    <div class="row g-0 pt-2">
                        <span class="col-6 col-sm-6 cart-subtotal-title fs-6"><strong>مجموع</strong></span>
                        <span class="col-6 col-sm-6 cart-subtotal-title fs-5 cart-subtotal text-end text-primary">
                            <b class="money">311 هزار</b>
                        </span>
                    </div>
                </div>
            </div>
            <!--Cart Summary-->
        </div>
        <div class="col-12 col-sm-12 col-md-12 col-lg-6">
            <!--Order Address-->
            <div class="block">
                <div class="block-content">
                    <div class="row g-0">
                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <div class="shipping-details mb-4 mb-sm-0">
                            <h3 class="title mb-3">آدرس حمل و نقل</h3>
                            <p>خیابان گالکسی انکو شماره 40 133/2،</p>
                            <p>ایران</p>
                            <p>مازندران - بابل</p>
                            <p>00004-1988</p>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <div class="billing-details">
                            <h3 class="title mb-3">آدرس صورتحساب</h3>
                            <p>خیابان گالکسی انکو شماره 40 133/2،</p>
                            <p>ایران</p>
                            <p>مازندران - بابل</p>
                            <p>00004-1988</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Order Address-->

            <!--Order Method-->
            <div class="block mt-4">
                <div class="block-content">
                    <div class="row g-0">
                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <div class="shipping-details mb-4 mb-sm-0">
                            <h3 class="title mb-3">روش حمل و نقل</h3>
                            <p>نرخ ثابت - ثابت</p>
                            <p>تاریخ تحویل: N/A</p>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <div class="billing-details">
                            <h3 class="title mb-3">روش پرداخت</h3>
                            <p>چک / سفارش پول</p>
                            <p>نقد هنگام تحویل</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block mt-4">
                <div class="block-content">
                    <div class="row g-0">
                    <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                        <div class="shipping-details mb-4 mb-sm-0">
                        <h3 class="title mb-3">جزئیات سفارش</h3>
                        <p>شناسه سفارش: GHERT05764</p>
                        <p>تاریخ سفارش: 14 شهریور 1402</p>
                        <p>مجموع سفارش: 311.00هزار</p>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                        <div class="billing-details">
                        <h3 class="title mb-3">تاریخ مورد انتظار تحویل</h3>
                        <p>سفارش شما در راه است</p>
                        <p class="h5 my-2">18 شهریور 1402</p>
                        <p><a href="#" class="btn-link">ردیابی سفارش</a></p>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
            <div class="d-flex-wrap w-100 mt-4 text-center">
                <button type="button" class="d-inline-flex align-items-center btn me-2 mb-2 me-sm-3">
                    <i class="me-2 icon an-print"></i>سفارش چاپ
                </button>
            </div>
        </div>
    </div>
</div>
@endsection