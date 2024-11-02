@extends('front.layouts.master')
@section('body_class') template-product product-layout1 @endsection
@section('styles')
<style>
.modal-content {  
    border-radius: 8px; 
    padding: 20px;
}  
.modal-header {  
    border-bottom: none;   
}  
.modal-body {  
    padding: 20px;
}  
.btn-auth {  
    background-color: #007bff;
    border-color: #007bff;
}  
.mt-3 {  
    margin-top: 15px; 
}  
</style>
@endsection
@section('content')
<div class="page-header mt-0 py-3">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                <div class="breadcrumbs">
                    <a href="/" title="Back to the home page">صفحه اصلی</a>
                    <a href="{{route('products.index')}}" title="Back to the home page">
                        <i class="icon anm anm-angle-left-l"></i>
                        محصولات</a>
                    <span class="main-title fw-bold">
                        <i class="icon anm anm-angle-left-l"></i>
                        جزئیات محصول
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container">
<div class="product-single">
    <div class="row">
    <div class="col-lg-4 col-md-4 col-sm-12 col-12 product-layout-img mb-4 mb-md-0">
        <!-- Product Horizontal -->
        <div class="product-details-img product-horizontal-style">
        <!-- Product Main -->
        <div class="zoompro-wrap">
            <!-- Product Image -->
            <div class="zoompro-span">
            <img
                id="zoompro"
                class="zoompro"
                src="{{asset('front/assets/images/products/product1.jpg')}}"
                data-zoom-image="{{asset('front/assets/images/products/product1.jpg')}}"
                alt="محصول"
                width="625"
                height="808"
            />
            </div>
            <!-- End Product Image -->
            <!-- Product Label -->
            @if ($product->status == 'available')
            <div class="product-labels">
                <span class="lbl on-sale">فروش</span>
            </div>
                
            @endif
            <!-- End Product Label -->
            <!-- Product Buttons -->
            <div class="product-buttons">
            <a
                href="#;"
                class="btn btn-primary prlightbox"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="زوم کردن"
                ><i class="icon anm anm-expand-l-arrows"></i
            ></a>
            </div>
            <!-- End Product Buttons -->
        </div>
        <!-- End Product Main -->

        <!-- Product Thumb -->
        <div class="product-thumb product-horizontal-thumb mt-3">
            <div
            id="gallery"
            class="product-thumb-horizontal"
            dir="ltr"
            >
            <a
                data-image="{{asset('front/assets/images/products/product1.jpg')}}"
                data-zoom-image="{{asset('front/assets/images/products/product1.jpg')}}"
                class="slick-slide slick-cloned active"
            >
                <img
                class="blur-up lazyload"
                data-src="{{asset('front/assets/images/products/product1.jpg')}}"
                src="{{asset('front/assets/images/products/product1.jpg')}}"
                alt="محصول"
                width="625"
                height="808"
                />
            </a>
            <a
                data-image="{{asset('front/assets/images/products/product1-1.jpg')}}"
                data-zoom-image="{{asset('front/assets/images/products/product1-1.jpg')}}"
                class="slick-slide slick-cloned"
            >
                <img
                class="blur-up lazyload"
                data-src="{{asset('front/assets/images/products/product1-1.jpg')}}"
                src="{{asset('front/assets/images/products/product1-1.jpg')}}"
                alt="محصول"
                width="625"
                height="808"
                />
            </a>
            <a
                data-image="{{asset('front/assets/images/products/product1-2.jpg')}}"
                data-zoom-image="{{asset('front/assets/images/products/product1-2.jpg')}}"
                class="slick-slide slick-cloned"
            >
                <img
                class="blur-up lazyload"
                data-src="{{asset('front/assets/images/products/product1-2.jpg')}}"
                src="{{asset('front/assets/images/products/product1-2.jpg')}}"
                alt="محصول"
                width="625"
                height="808"
                />
            </a>
            <a
                data-image="{{asset('front/assets/images/products/product1-3.jpg')}}"
                data-zoom-image="{{asset('front/assets/images/products/product1-3.jpg')}}"
                class="slick-slide slick-cloned"
            >
                <img
                class="blur-up lazyload"
                data-src="{{asset('front/assets/images/products/product1-3.jpg')}}"
                src="{{asset('front/assets/images/products/product1-3.jpg')}}"
                alt="محصول"
                width="625"
                height="808"
                />
            </a>
            <a
                data-image="{{asset('front/assets/images/products/product1-4.jpg')}}"
                data-zoom-image="{{asset('front/assets/images/products/product1-4.jpg')}}"
                class="slick-slide slick-cloned"
            >
                <img
                class="blur-up lazyload"
                data-src="{{asset('front/assets/images/products/product1-4.jpg')}}"
                src="{{asset('front/assets/images/products/product1-4.jpg')}}"
                alt="محصول"
                width="625"
                height="808"
                />
            </a>
            <a
                data-image="{{asset('front/assets/images/products/product1-5.jpg')}}"
                data-zoom-image="{{asset('front/assets/images/products/product1-5.jpg')}}"
                class="slick-slide slick-cloned"
            >
                <img
                class="blur-up lazyload"
                data-src="{{asset('front/assets/images/products/product1-5.jpg')}}"
                src="{{asset('front/assets/images/products/product1-5.jpg')}}"
                alt="محصول"
                width="625"
                height="808"
                />
            </a>
            </div>
        </div>
        <!-- End Product Thumb -->

        <!-- Product Gallery -->
        <div class="lightboximages">
            <a
            href="{{asset('front/assets/images/products/product1.jpg')}}"
            data-size="1000x1280"
            ></a>
            <a
            href="{{asset('front/assets/images/products/product1-1.jpg')}}"
            data-size="1000x1280"
            ></a>
            <a
            href="{{asset('front/assets/images/products/product1-2.jpg')}}"
            data-size="1000x1280"
            ></a>
            <a
            href="{{asset('front/assets/images/products/product1-3.jpg')}}"
            data-size="1000x1280"
            ></a>
            <a
            href="{{asset('front/assets/images/products/product1-4.jpg')}}"
            data-size="1000x1280"
            ></a>
            <a
            href="{{asset('front/assets/images/products/product1-5.jpg')}}"
            data-size="1000x1280"
            ></a>
        </div>
        <!-- End Product Gallery -->
        </div>
        <!-- End Product Horizontal -->
    </div>

    <div
        class="col-lg-8 col-md-8 col-sm-12 col-12 product-layout-info"
    >
        <!-- Product Details -->
        <div class="product-single-meta">
            <h2 id="title" class="product-main-title">{{ $product->title }}</h2>
            <!-- Product Reviews -->
            <div class="product-review d-flex-center mb-3">
                <div class="reviewStar d-flex-center">
                @for ($i = 0; $i < $averageStar; $i++)  
                    <i class="icon anm anm-star"></i>  
                @endfor  
                @for ($i = $averageStar; $i < 5; $i++)
                    <i class="icon anm anm-star"></i>  
                @endfor  
                <span class="caption me-2">24 بررسی ها</span>
                </div>
                <a class="reviewLink d-flex-center" href="#reviews">یک نظر بنویسید</a>

            </div>
            <!-- End Product Reviews -->
            <!-- Product Info -->
            <div class="product-info">
                <p class="product-stock d-flex">
                دسترسی:
                <span class="pro-stockLbl ps-0">
                    <span class="d-flex-center stockLbl instock {{$product->status == 'available' ? 'text-uppercase' : 'text-danger' }}">
                        {{$product->getStatusLabelAttribute($product->status)}} در انبار
                    </span>
                </span>
                </p>

                <p class="product-type">
                دسته بندی :<span class="text">
                @foreach($product->categories as $category)  
                    {{ $category->title }}@if(!$loop->last), @endif  
                @endforeach </span>
                </p>
                <p class="product-sku">
                شناسه:<span class="text">{{ $product->id }}</span>
                </p>
            </div>
            @php  
                function formatPrice($price) {  
                    if ($price >= 1_000_000) {  
                        return number_format($price / 1_000_000) . ' میلیون تومان';  
                    } elseif ($price >= 1_000) {  
                        return number_format($price / 1_000) . ' هزار تومان';  
                    } else {  
                        return number_format($price) . ' تومان';  
                    }  
                }  
            @endphp  
            @if ($product->discount)
                <div class="product-price d-flex-center my-3">
                    <span class="price old-price" id="price">{{ formatPrice($product->unit_price) }}</span><span class="price">99 هزار</span>
                </div>
                @else
                <div class="product-price d-flex-center my-3">
                    <span class="price" id="price">{{ formatPrice($product->unit_price) }}</span>
                </div>
            @endif
        </div>
        <form method="post" action="" class="product-form product-form-border hidedropdown">
        @csrf
        <!-- Swatches -->
        <div class="product-swatches-option">
            <!-- Swatches Color -->
            <div
            class="product-item swatches-image w-100 mb-4 swatch-0 option1"
            data-option-index="0"
            >
            {{-- <label class="label d-flex align-items-center">رنگ ها:</label> --}}
                {{-- <ul class="variants-clr swatches d-flex-center pt-1 clearfix">
                @foreach ($product->varieties as $variety)
                    @php
                        $class = $variety->store_balance > 1 ? 'available' : ($variety->store_balance === 0 || is_null($variety->store_balance) ? 'soldout' : '');
                    @endphp
                    <li class="swatch x-large {{$class}} {{$loop->first ? 'active' : ''}}">
                    <img
                        src="{{ asset('front/assets/images/products/product1-1-80x.jpg') }}"
                        alt="تصویر"
                        width="80"
                        height="80"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                    />
                    </li>
                @endforeach
                </ul> --}}
            </div>
            <!-- End Swatches Color -->
            <!-- Swatches Size -->
            <div
            class="product-item swatches-size w-100 mb-4 swatch-1 option2"
            data-option-index="1"
            >
            {{-- <label class="label d-flex align-items-center"
                >اندازه:<span class="slVariant me-1 fw-bold">S</span>
                <a
                href="#sizechart-modal"
                class="text-link sizelink text-muted size-chart-modal"
                data-bs-toggle="modal"
                data-bs-target="#sizechart_modal"
                >راهنمای اندازه</a
                ></label
            > --}}
            <input type="hidden" id="varietyPrice" name="varietyPrice" value="">
            <input type="hidden" id="varietyValue" name="varietyValue" value="">
            <ul
                class="variants-size size-swatches d-flex-center pt-1 clearfix"
            >
            @if ($product->varieties_showcase['attributes'])
                @foreach ($product->varieties_showcase['attributes'][0]['modelDetails'] as $item)  
                    @php  
                        $variety = Modules\Product\Entities\Variety::find($item['myAvailableVarietyIds'][0]);  
                        $class = $variety->store_balance > 1 ? 'available' : ($variety->store_balance === 0 || is_null($variety->store_balance) ? 'soldout' : '');  
                    @endphp  
                    <input type="hidden" id="variety_id" name="variety_id" value="">
                    <li class="swatch x-large {{ $class }}" data-item-id="{{ $item['myAvailableVarietyIds'][0] }}" data-item-value="{{ $item['value'] }}" data-item-price="{{ $variety['price'] }}">  
                        <span  
                            class="swatchLbl"  
                            data-bs-toggle="tooltip"  
                            data-bs-placement="top"  
                            title="{{ $item['value'] }}"  
                        >{{ $item['value'] }}</span>  
                    </li>  
                @endforeach 
                @endif
                {{-- <li class="swatch x-large available active">
                <span
                    class="swatchLbl"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    title="S"
                    >S</span
                >
                </li>
                <li class="swatch x-large available">
                <span
                    class="swatchLbl"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    title="M"
                    >M</span
                >
                </li>
                <li class="swatch x-large available">
                <span
                    class="swatchLbl"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    title="L"
                    >L</span
                >
                </li>
                <li class="swatch x-large available">
                <span
                    class="swatchLbl"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    title="XL"
                    >XL</span
                >
                </li> --}}
            </ul>
            </div>
            <!-- End Swatches Size -->
        </div>
        <!-- End Swatches -->

        <!-- Product Action -->
        <div class="product-action w-50 d-flex-wrap my-3 my-md-4">
            <!-- Product Quantity -->
            <div class="product-form-quantity d-flex-center">
                <div class="qtyField">
                    <a class="qtyBtn minus" onclick="decrease()">
                        <i style="cursor: pointer" class="icon anm anm-minus-r"></i>
                    </a>
                    <input class="product-form-input qty" type="number" id="quantity" value="1" min="1" readonly>
                    <a class="qtyBtn plus" onclick="increase()">
                        <i style="cursor: pointer" class="icon anm anm-plus-r"></i>
                    </a>
                </div>
            </div>
            <!-- End Product Quantity -->
            <!-- Product Add -->
            <div class="product-form-submit addcart fl-1 me-3">
            @if($product->status == 'available')
            <button  
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#minicart-drawer"
                name="add"  
                class="btn btn-secondary product-form-cart-submit">  
                <span>افزودن به سبد</span>  
            </button>  
            @else
            <button
                type="button"
                name="add"
                class="btn btn-secondary product-form-sold-out"
                disabled="disabled"
            >
                <span>ناموجود</span>
            </button>
            @endif
            </div>
        </div>
        <p class="infolinks d-flex-center justify-content-between">
            @if (auth()->guard('customer')->user()->favorites()->where('product_id', $product->id)->exists())
            <a class="text-link wishlist" style="cursor: pointer" id="wishlistBtn">  
                <i id="favicon" class="icon anm anm-heart ms-2"></i>  
                <span>افزودن به فهرست علاقه مندی ها</span>  
            </a>  
            @else
            <a class="text-link wishlist" style="cursor: pointer" id="wishlistBtn">  
                <i id="favicon" class="icon anm anm-heart-l ms-2"></i>  
                <span>افزودن به فهرست علاقه مندی ها</span>  
            </a>  
            @endif
        </p>
    </form>
            <div class="modal fade" id="loginModalProduct" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">  
                <div class="modal-dialog modal-md" role="document">  
                    <div class="modal-content">  
                        <div class="modal-header">  
                            <h5 class="modal-title font-weight-bold">لطفاً وارد حساب کاربری خود شوید.</h5>  
                            <button type="button" class="close" id="closeButtonProduct1" aria-label="Close">  
                                <span aria-hidden="true">&times;</span>  
                            </button>  
                        </div>  
                        <div class="modal-body text-center">  
                            <a href="{{ route('pageRegisterLogin') }}" class="btn btn-primary btn-auth">ورود به حساب کاربری</a>  
                            <button type="button" class="btn btn-outline-danger" id="closeButtonProduct2">بستن</button>  
                        </div>  
                    </div>  
                </div>  
            </div>
            <div class="social-sharing d-flex-center mt-2 lh-lg">
                <span class="sharing-lbl fw-600">اشتراک گذاری:</span>
                <a
                    href="#"
                    class="d-flex-center btn btn-link btn--share share-facebook"
                    ><i class="icon anm anm-facebook-f"></i
                    ><span class="share-title">فیس بوک</span></a
                >
                <a
                    href="#"
                    class="d-flex-center btn btn-link btn--share share-twitter"
                    ><i class="icon anm anm-twitter"></i
                    ><span class="share-title">توئیت</span></a
                >
                <a
                    href="#"
                    class="d-flex-center btn btn-link btn--share share-pinterest"
                    ><i class="icon anm anm-pinterest-p"></i>
                    <span class="share-title">آن را پین کنید</span></a
                >
                <a
                    href="#"
                    class="d-flex-center btn btn-link btn--share share-linkedin"
                    ><i class="icon anm anm-linkedin-in"></i
                    ><span class="share-title">Linkedin</span></a
                >
                <a
                    href="#"
                    class="d-flex-center btn btn-link btn--share share-email"
                    ><i class="icon anm anm-envelope-l"></i
                    ><span class="share-title">ایمیل</span></a
                >
            </div>
        </div>
    </div>
</div>
<div class="tabs-listing section pb-0">
    <ul
    class="product-tabs style1 list-unstyled d-flex-wrap d-flex-justify-center d-none d-md-flex"
    >
    <li rel="description" class="active">
        <a class="tablink">توضیحات</a>
    </li>
    <li rel="additionalInformation">
        <a class="tablink">مشخصات</a>
    </li>

    <li rel="reviews"><a class="tablink">بررسی ها</a></li>
    </ul>

    <div class="tab-container">
    <!--Description-->
    <h3 class="tabs-ac-style d-md-none active" rel="description">
        شرح
    </h3>
    <div id="description" class="tab-content">
        <div class="product-description">
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                {!! $product->description !!}
            </div>
        </div>
        </div>
    </div>
    <!--End Description-->

    <!--Additional Information-->
    <h3 class="tabs-ac-style d-md-none" rel="additionalInformation">
        اطلاعات تکمیلی
    </h3>
    <div id="additionalInformation" class="tab-content">
        <div class="product-description">
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-4 mb-md-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle table-part mb-0">
                    @forelse ($product->specifications as $specification)
                    <tr>
                        <th>{{ $specification->label }}</th>
                        <td>
                            @foreach($specification->values as $item)  
                            {{ $item->value }}@if(!$loop->last), @endif  
                            @endforeach
                        </td>
                    </tr>
                    @empty
                    @endforelse
                    </table>
                </div>
            </div>
        </div>
        </div>
    </div>
    <!--End Additional Information-->

    <!--Review-->
    <h3 class="tabs-ac-style d-md-none" rel="reviews">بررسی</h3>
    <div id="reviews" class="tab-content">
        <div class="row">
        <div class="col-12 col-sm-12 col-md-12 col-lg-6 mb-4">
            <div class="ratings-main">
            <div class="avg-rating d-flex-center mb-3">
                <h4 class="avg-mark">{{$averageStar}}</h4>
                <div class="avg-content me-3">
                <p class="text-rating">میانگین امتیاز</p>
                <div class="ratings-full product-review">
                    <a class="reviewLink d-flex-center" href="#reviews">
                        @php($maxStars = 5)  
                        @for ($i = 0; $i < $maxStars; $i++)  
                            @if ($i < $averageStar)  
                                <i class="icon anm anm-star"></i>
                            @else  
                                <i class="icon anm anm-star-o"></i> 
                            @endif  
                        @endfor  
                    </a>
                </div>
                </div>
            </div>
            </div>
            <hr />
            <div class="spr-reviews">
            <h3 class="spr-form-title">نظرات مشتریان</h3>
            <div class="review-inner">
                @forelse ($product->productComments as $comments)
                <div class="spr-review d-flex w-100">
                <div class="spr-review-profile flex-shrink-0">
                    <img
                    class="blur-up lazyload"
                    data-src="assets/images/users/testimonial2.jpg"
                    src="assets/images/users/testimonial2.jpg"
                    alt=""
                    width="200"
                    height="200"
                    />
                </div>
                <div class="spr-review-content flex-grow-1">
                    <div
                    class="d-flex justify-content-between flex-column mb-2"
                    >
                    <div
                        class="title-review d-flex align-items-center justify-content-between"
                    >
                        <h5 class="spr-review-header-title text-transform-none mb-0">
                        {{$comments->creator->first_name || $comments->creator->last_name ? $comments->creator->first_name . ' ' . $comments->creator->last_name : '...' }}
                        </h5>
                        <span class="product-review spr-starratings m-0">
                            <span class="reviewLink">
                                @php($maxStars = 5)  
                                @for ($i = 0; $i < $maxStars; $i++)  
                                    @if ($i < $comments->rate)  
                                        <i class="icon anm anm-star"></i>  <!-- ستاره پر -->  
                                    @else  
                                        <i class="icon anm anm-star-o"></i> <!-- ستاره خالی -->  
                                    @endif  
                                @endfor  
                            </span>
                        </span>
                        </div>
                    </div>
                    <b class="head-font">{{$comments->title}}</b>
                    <p class="spr-review-body">{{$comments->body}}</p>
                    </div>
                </div>
                @empty
                <p>نظری برای این محصول ثبت نشده.</p>
                @endforelse
            </div>
            </div>
        </div>

        <div class="col-12 col-sm-12 col-md-12 col-lg-6 mb-4">
            <form id="commentForm" action="{{ route('product-comments.store') }}" method="post" class="product-review-form new-review-form">  
                @csrf  
                <div id="statusDanger" class="alert alert-danger d-none">  
                    خطا در ثبت نظر.  
                </div>  
                <h3 class="spr-form-title">نظری بنویسید</h3>  
                <fieldset class="row spr-form-contact">  
                    <div class="col-sm-6 spr-form-review-title form-group">  
                        <label class="spr-form-label" for="review">عنوان</label>  
                        <input class="spr-form-input spr-form-input-text" id="review" type="text" name="title" />  
                    </div>  
                    <div class="col-sm-6 spr-form-review-rating form-group">  
                        <label class="spr-form-label">رتبه بندی</label>  
                        <div class="product-review pt-1">  
                            <div class="review-rating">  
                                @for ($i = 0; $i < 5; $i++)  
                                    <span class="star" style="cursor: pointer" data-value="{{ $i + 1 }}">  
                                        <i class="icon anm anm-star-o"></i>  
                                    </span>  
                                @endfor  
                            </div>  
                            <input type="hidden" name="rate" id="rating" value="0">  
                            <input type="hidden" name="product_id" value="{{ $product->id }}">  
                            <input type="hidden" name="show_customer_name" value="1">  
                        </div>  
                    </div>  
                    <div class="col-12 spr-form-review-body form-group">  
                        <label class="spr-form-label" for="message">توضیحات</label>  
                        <div class="spr-form-input">  
                            <textarea class="spr-form-input spr-form-input-textarea" id="message" name="body" rows="3"></textarea>  
                        </div>  
                    </div>  
                </fieldset>  
                <div class="spr-form-actions clearfix">  
                    <input type="button" class="btn btn-primary spr-button spr-button-primary" value="ارسال نظر" />  
                </div>  
            </form>  
        </div>
        </div>
        <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">  
            <div class="modal-dialog modal-md" role="document">  
                <div class="modal-content">  
                    <div class="modal-header">  
                        <h5 class="modal-title font-weight-bold">لطفاً وارد حساب کاربری خود شوید.</h5>  
                        <button type="button" class="close" id="closeButton" aria-label="Close">  
                            <span aria-hidden="true">&times;</span>  
                        </button>  
                    </div>  
                    <div class="modal-body text-center">  
                        <a href="{{ route('pageRegisterLogin') }}" class="btn btn-primary btn-auth">ورود به حساب کاربری</a>  
                        <button type="button" class="btn btn-outline-danger" id="closeButton2">بستن</button>  
                    </div>  
                </div>  
            </div>  
        </div>
    </div>
    <!--End Review-->
    </div>
</div>
<!--End Product Tabs-->
</div>
<div class="pswp" tabindex="-1" role="dialog">
    <div class="pswp__bg"></div>
        <div class="pswp__scroll-wrap">
        <div class="pswp__container">
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
        </div>
        <div class="pswp__ui pswp__ui--hidden">
            <div class="pswp__top-bar">
            <div class="pswp__counter"></div>
            <button
                class="pswp__button pswp__button--close"
                title="بستن (Esc)"
            ></button>
            <button
                class="pswp__button pswp__button--share"
                title="اشتراک گذاری"
            ></button>
            <button
                class="pswp__button pswp__button--fs"
                title="تمام صفحه را تغییر دهید"
            ></button>
            <button
                class="pswp__button pswp__button--zoom"
                title="بزرگنمایی/کوچکنمایی"
            ></button>
            <div class="pswp__preloader">
                <div class="pswp__preloader__icn">
                <div class="pswp__preloader__cut">
                    <div class="pswp__preloader__donut"></div>
                </div>
                </div>
            </div>
            </div>
            <div
            class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap"
            >
            <div class="pswp__share-tooltip"></div>
            </div>
            <button
            class="pswp__button pswp__button--arrow--left"
            title="قبلی (پیکان سمت چپ)"
            ></button>
            <button
            class="pswp__button pswp__button--arrow--right"
            title="بعدی (پیکان سمت راست)"
            ></button>
            <div class="pswp__caption">
            <div class="pswp__caption__center"></div>
            </div>
        </div>
    </div>
</div>
</div>
<section class="section product-slider pb-0">
    <div class="container">
        <div class="section-header">
            <h2>محصولات مشترک</h2>
        </div>
        <div class="grid-products product-slider-4items gp15 arwOut5 hov-arrow" dir="ltr">
            @forelse ($relatedProducts1 as $product)
            <div class="item col-item">
                <div class="product-box">
                    <div class="product-image">
                        <!-- شروع تصویر محصول -->
                        <a href="{{route('products.show',$product->id)}}" class="product-img">
                            <img
                                class="primary blur-up lazyload"
                                data-src="{{asset('front/assets/images/products/cosmetic-product1.jpg')}}"
                                src="{{asset('front/assets/images/products/cosmetic-product1.jpg')}}"
                                alt="محصول"
                                title="محصول"
                                width="625"
                                height="703"
                            />
                            <img
                                class="hover blur-up lazyload"
                                data-src="{{asset('front/assets/images/products/cosmetic-product1-1.jpg')}}"
                                src="{{asset('front/assets/images/products/cosmetic-product1-1.jpg')}}"
                                alt=" محصول"
                                title="محصول"
                                width="625"
                                height="703"
                            />
                        </a>
                        @php($finalPrice = $product->major_final_price)
                        @php($hasDiscount = $finalPrice->discount_price > 0 ? true : false)
                        <div class="product-labels">
                            @if ($finalPrice->discount_type === 'percentage')
                                <span class="lbl on-sale">{{ $finalPrice->discount . '%' }} تخفیف</span>
                            @elseif($finalPrice->discount_type)
                                <span class="lbl on-sale">{{ number_format($finalPrice->discount_price) . ' تومان' }} تخفیف</span>
                            @endif
                        </div>
                        <!-- برچسب محصول نهایی -->
                    </div>
                    <div class="product-details text-center">
                        <!-- نام محصول -->
                        <div class="product-name">
                        <a href="{{route('products.show',$product->id)}}">{{$product->title}}</a>
                        </div>
                        <!-- نام محصول نهایی -->
                        <!-- قیمت محصول -->
                        <div class="product-price">
                            @if ($hasDiscount)
                                <span class="price old-price">{{ number_format($finalPrice->amount) }} تومان </span>
                            @endif
                            <span class="price">{{ number_format($finalPrice->amount) }} تومان </span>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <p>محصولی یافت نشد</p>
            @endforelse
        </div>
    </div>
</section>

@endsection
@section('scripts')
<script src="{{ asset('front/assets/js/vendor/jquery.elevatezoom.js') }}"></script>
<script src="{{ asset('front/assets/js/vendor/photoswipe.min.js') }}"></script>
    <script>
        function formatPrice(price) {  
            if (price >= 1000000) {  
                return Math.floor(price / 1000000) + ' میلیون تومان';  
            } else if (price >= 1000) {  
                return Math.floor(price / 1000) + ' هزار تومان';  
            } else {  
                return price + ' تومان';  
            }  
        }  
        document.addEventListener('DOMContentLoaded', function() {  
            const swatchItems = document.querySelectorAll('.swatch');  
            swatchItems.forEach(item => {  
                item.addEventListener('click', function() {  
                    const itemValue = this.getAttribute('data-item-value');  
                    const itemPrice = this.getAttribute('data-item-price');  
                    document.getElementById('price').innerText = formatPrice(itemPrice);  
                    document.getElementById("varietyValue").value = itemValue;
                    document.getElementById("varietyPrice").value = itemPrice;
                });  
            });  
        });  
        function increase() {
            const quantityInput = document.getElementById('quantity');
            quantityInput.value = parseInt(quantityInput.value) + 1;
        }
            
        function decrease() {
            const quantityInput = document.getElementById('quantity');
            if (parseInt(quantityInput.value) > 1) {
                quantityInput.value = parseInt(quantityInput.value) - 1;
            }
        }
        $(document).ready(function () {
            $('.swatch').on('click', function() {  
                var $this = $(this);  
                var selectedId = $this.data('item-id');
                var selectedPrice = document.getElementById("varietyPrice").value;
                var selectedValue = document.getElementById("varietyValue").value;
                var title = document.getElementById("title").value;
                var selectedQuantity = document.getElementById("quantity").value;
                $('#variety_id').val(selectedId);  
            });  


            updateCartDisplay();  

            $('.product-form-cart-submit').on('click', function(event) {  
                event.preventDefault();  
                let isLoggedIn = @json(auth()->user());  
                
                if (!isLoggedIn) {  
                    $('#loginModalProduct').modal('show');  
                } else {  
                    let variety_id = $('#variety_id').val();  
                    let varietyQuantity = $('#quantity').val();  
                    let titleProduct = $('#title').text();  
                    let productImage = '{{ asset('front/assets/images/products/product1-1-80x.jpg') }}';  
                    let varietyValue = $('#varietyValue').val();  
                    let varietyPrice = $('#price').text();  

                    $.ajax({  
                        url: `{{ route('cart.add') }}/${variety_id}`,  
                        type: 'POST',  
                        headers: {  
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',  
                        },  
                        data: {  
                            variety_id: variety_id,  
                            quantity: varietyQuantity,  
                        },  
                        success: function(response) {  
                            let productData = getCookie('productData');  
                            productData = productData ? JSON.parse(decodeURIComponent(productData)) : [];  

                            // اضافه کردن یا به‌روزرسانی محصول در productData  
                            const existingProduct = productData.find(product => product.variety_id === variety_id);  
                            if (existingProduct) {  
                                existingProduct.variety_quantity = parseInt(existingProduct.variety_quantity) + parseInt(varietyQuantity);  
                            } else {  
                                productData.push({  
                                    variety_id: variety_id,  
                                    variety_quantity: varietyQuantity,  
                                    title_product: titleProduct,  
                                    product_image: productImage,  
                                    variety_value: varietyValue,  
                                    variety_price: varietyPrice,  
                                });  
                            }  

                            document.cookie = `productData=${encodeURIComponent(JSON.stringify(productData))}; path=/;`;  
                            
                            // به‌روزرسانی سبد خرید در صفحه  
                            updateCartDisplay();  

                            Swal.fire({  
                                icon: "success",  
                                text: response.message  
                            });  
                        },  
                        error: function(error) {  
                            console.log(error.responseJSON.message);  
                            Swal.fire({  
                                icon: "error",  
                                text: error.responseJSON.message  
                            });  
                        }  
                    });  
                }  
            });  
// تابع برای به‌روزرسانی نمایش سبد خرید  
function updateCartDisplay() {  
let productData = getCookie('productData');  
if (productData) {  
    productData = JSON.parse(decodeURIComponent(productData));  
    let totalItems = productData.length;  
    $('#cart-count').text(`سبد خرید شما (${totalItems} مورد)`);  
    $('#num-cart-count').text(`${totalItems}`);  

    $('#output').empty(); // خالی کردن لیست قبلی  
    let totalPrice = 0; // جمع کل قیمت‌ها  

    productData.forEach(function(product) {  
        let varietyPrice = parseFloat(product.variety_price) * 1000; // تبدیل قیمت به تومان  
        let quantity = parseInt(product.variety_quantity); // تعداد  
        let productTotalPrice = Math.floor(varietyPrice * quantity); // قیمت کل محصول  
        totalPrice += productTotalPrice;  

        let productHtml = `  
        <li class="item d-flex justify-content-center align-items-center" data-variety-id="${product.variety_id}">  
            <a class="product-image rounded-0" href="product-layout1.html">  
                <img  
                    class="rounded-0 blur-up lazyload"  
                    data-src="${product.product_image}"  
                    src="${product.product_image}"  
                    alt="product"  
                    title="محصول"  
                    width="120"  
                    height="170"  
                />  
            </a>  
            <div class="product-details">  
                <a class="product-title" href="product-layout1.html">${product.title_product}</a>  
                <div class="variant-cart my-2">${product.variety_value}</div>  
                <div class="priceRow">  
                    <div class="product-price">  
                        <span class="price">${product.variety_price}</span>  
                    </div>  
                </div>  
            </div>  
            <div class="qtyDetail text-center">  
                <div class="qtyField">  
                    <a class="qtyBtn minus" onclick="decreaseQuantity(this)">  
                        <i style="cursor: pointer" class="icon anm anm-minus-r"></i>  
                    </a>  
                    <input type="text" name="quantity"   
                        value="${product.variety_quantity}"   
                        class="qty"   
                        data-cart-id=""   
                        data-key="${product.variety_id}"   
                        readonly/>  
                    <a class="qtyBtn plus" onclick="increaseQuantity(this)">  
                        <i style="cursor: pointer" class="icon anm anm-plus-r"></i>  
                    </a>  
                </div>  
                <a href="#" class="edit-i remove" onclick="removeVariety(event, '${product.variety_id}')" data-variety-id="${product.variety_id}">  
                    <i class="icon anm anm-times-r" data-bs-toggle="tooltip" data-bs-placement="top" title="حذف"></i>  
                </a>  
            </div>  
        </li>`;  

        // اضافه کردن محصول به لیست  
        $('#output').append(productHtml);  
    });  

    console.log("Total Price before formatting: ", totalPrice);  
    let totalPriceFormatted = formatPrice(totalPrice);  
    $('#cart-price').text(totalPriceFormatted);  
}  
}  

// تابع برای گرفتن کوکی  
function getCookie(name) {  
let cookieArr = document.cookie.split(";");  

for (let i = 0; i < cookieArr.length; i++) {  
    let cookiePair = cookieArr[i].split("=");  
    if (name == cookiePair[0].trim()) {  
        return decodeURIComponent(cookiePair[1]);  
    }  
}  

return null;  
}  

// کاهش و افزایش مقدار محصول  
function decreaseQuantity(element) {  
let input = $(element).siblings('input.qty');  
let currentQuantity = parseInt(input.val());  

if (currentQuantity > 1) {  
    currentQuantity--;  
    input.val(currentQuantity);  

    // به‌روز کردن مقدار در کوکی  
    updateProductQuantity(input.data('key'), currentQuantity);  
    updateCartDisplay(); // به‌روزرسانی سبد خرید  
}  
}  

function increaseQuantity(element) {  
let input = $(element).siblings('input.qty');  
let currentQuantity = parseInt(input.val());  
currentQuantity++;  
input.val(currentQuantity);  

// به‌روز کردن مقدار در کوکی  
updateProductQuantity(input.data('key'), currentQuantity);  
updateCartDisplay(); // به‌روزرسانی سبد خرید  
}  

// تابع برای به‌روزرسانی مقدار محصول در کوکی  
function updateProductQuantity(variety_id, quantity) {  
let productData = getCookie('productData');  
if (productData) {  
    productData = JSON.parse(decodeURIComponent(productData));  
    const product = productData.find(p => p.variety_id === variety_id);  
    if (product) {  
        product.variety_quantity = quantity;  
        document.cookie = `productData=${encodeURIComponent(JSON.stringify(productData))}; path=/;`;  
    }  
}  
}  

// تابع حذف محصول از سبد خرید  
function removeVariety(event, variety_id) {  
event.preventDefault();  
let productData = getCookie('productData');  

if (productData) {  
    productData = JSON.parse(decodeURIComponent(productData));  
    productData = productData.filter(product => product.variety_id !== variety_id); // فیلتر کردن محصول  
    document.cookie = `productData=${encodeURIComponent(JSON.stringify(productData))}; path=/;`;  
    
    updateCartDisplay(); // به‌روزرسانی سبد خرید  
}  
}  
            function formatPrice(price) {  
                let millionPart = Math.floor(price / 1000000);
                let thousandPart = Math.floor((price % 1000000) / 1000);
                let result = '';

                if (millionPart > 0) {
                    result += millionPart + ' میلیون تومان';
                }
                if (thousandPart > 0) {
                    if (result) result += ' و ';  // برای جدا کردن بخش‌ها
                    result += thousandPart + ' هزار تومان';
                }
                
                return result || (price + ' تومان');
            }

            $('.spr-button-primary').on('click', function(event) {  
                event.preventDefault(); 

                let formData = {  
                    title: $('#review').val(),  
                    rate: $('#rating').val(),  
                    body: $('#message').val(),  
                    product_id: $('input[name="product_id"]').val(),  
                    show_customer_name: $('input[name="show_customer_name"]').val()  
                };  
                let isLoggedIn = @json(auth()->user());  
                if (!isLoggedIn) {  
                    $('#loginModalProduct').modal('show');  
                } else {  
                    $.ajax({  
                        url: '{{ route('product-comments.store') }}', 
                        method: 'POST',  
                        headers: {  
                            'X-CSRF-TOKEN': $('input[name="_token"]').val(),  
                        },  
                        data: formData,  
                        success: function(response) {  
                            $('#commentForm')[0].reset();
                            Swal.fire({  
                                icon: "success",  
                                text: "نظر با موفقیت ثبت شد و پس از تایید نمایش داده خواهد شد."  
                            });
                        },  
                        error: function(error) {  
                            Swal.fire({  
                                icon: "error",  
                                text: "خطا در ثبت نظر."  
                            });    
                        }  
                    });  
                }  
            });  
            $('#wishlistBtn').click(function(event) {  
                event.preventDefault();  
                var formData = $('#postForm').serialize();   
                let isLoggedIn = @json(auth()->user());  
                let $icon = $('favicon').find('i');  
                if ($icon.hasClass('anm-heart-l')) {  
                    if (!isLoggedIn) {  
                        $('#loginModalProduct').modal('show');  
                    } else {  
                        $.ajax({  
                            url: `{{ route('products.addToFavorites',$idProduct) }}`,  
                            type: 'POST',  
                            headers: {  
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',  
                            },  
                            success: function(response) {
                                if ($icon.hasClass('anm-heart-l')) {  
                                    console.log(1);
                                    $icon.removeClass('anm-heart-l').addClass('anm-heart');  
                                } else {  
                                    console.log(2);
                                    $icon.removeClass('anm-heart').addClass('anm-heart-l');  
                                }    
                                Swal.fire({  
                                    icon: "success",  
                                    text: response.message  
                                });  
                            },  
                            error: function(error) {  
                                console.log(error);  
                                Swal.fire({  
                                    icon: "error",  
                                    text: error.message || "An error occurred."  
                                });  
                            }  
                        });  
                    }  
                }else{
                    $.ajax({  
                        url: `{{ route('products.deleteFromFavorites', $product->id) }}`,  
                        type: 'DELETE',  
                        headers: {  
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',  
                        },  
                        success: function(response) {
                            let $icon = $(this).find('i');  
                            if ($icon.hasClass('anm-heart')) {  
                                $icon.removeClass('anm-heart').addClass('anm-heart-l');  
                            } else {  
                                $icon.removeClass('anm-heart-l').addClass('anm-heart');  
                            }    
                            Swal.fire({  
                                icon: "success",  
                                text: response.message  
                            });  
                        },  
                        error: function(error) {  
                            console.log(error);  
                            Swal.fire({  
                                icon: "error",  
                                text: error.message || "An error occurred."  
                            });  
                        }  
                    }); 
                }
            }); 
            $('#closeButtonProduct1').on('click', function() {  
                $('#loginModalProduct').removeClass('show'); 
            });  
            $('#closeButtonProduct2').on('click', function() {  
                $('#loginModalProduct').removeClass('show'); 
            });  
            $('#closeButton').on('click', function() {  
                $('#loginModal').removeClass('show'); 
            });  
            $('#closeButton2').on('click', function() {  
                $('#loginModal').removeClass('show'); 
            });  
            
        });  

            //star
            const $stars = $('.star');
            const $ratingInput = $('#rating');

            $stars.on('mouseover', function() {
                const index = $(this).data('value');
                $stars.each(function(i) {
                    $(this).html(i < index ? '<i class="icon anm anm-star"></i>' : '<i class="icon anm anm-star-o"></i>');
                });
                $ratingInput.val(index);
            });

            $stars.on('mouseout', function() {
                const currentRating = parseFloat($ratingInput.val());
                $stars.each(function(i) {
                    $(this).html(i < currentRating ? '<i class="icon anm anm-star"></i>' : '<i class="icon anm anm-star-o"></i>');
                });
            });

            $stars.each(function() {
                $(this).on('click', function() {
                    const ratingValue = $(this).data('value');
                    $stars.each(function(i) {
                        $(this).html(i < ratingValue ? '<i class="icon anm anm-star"></i>' : '<i class="icon anm anm-star-o"></i>');
                    });
                });
            });
            function product_zoom() {
                $(".zoompro").elevateZoom({
                    gallery: "gallery",
                    galleryActiveClass: "active",
                    zoomWindowWidth: 300,
                    zoomWindowHeight: 100,
                    scrollZoom: false,
                    zoomType: "inner",
                    cursor: "crosshair",
                });
            }
            product_zoom();
        $(function () {
            var $pswp = $(".pswp")[0],
                image = [],
                getItems = function () {
                var items = [];
                $(".lightboximages a").each(function () {
                    var $href = $(this).attr("href"),
                    $size = $(this).data("size").split("x"),
                    item = {
                        src: $href,
                        w: $size[0],
                        h: $size[1],
                    };
                    items.push(item);
                });
                return items;
                };
            var items = getItems();

            $.each(items, function (index, value) {
                image[index] = new Image();
                image[index].src = value["src"];
            });
            $(".prlightbox").on("click", function (event) {
                event.preventDefault();

                var $index = $(".active-thumb").parent().attr("data-slick-index");
                $index++;
                $index = $index - 1;

                var options = {
                index: $index,
                bgOpacity: 0.7,
                showHideOpacity: true,
                };
                var lightBox = new PhotoSwipe(
                $pswp,
                PhotoSwipeUI_Default,
                items,
                options
                );
                lightBox.init();
            });
        });
    </script>
@endsection
