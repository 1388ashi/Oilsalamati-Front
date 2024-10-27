@extends('front.layouts.master')
@section('content')
<x-front.breadcrumb :items="[['route_link' => 'products.index','title' => 'محصولات'],['title' => 'جزئیات محصول']]" />

<div class="container">
<!--Product Content-->
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
                title="Zoom Image"
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
            <h2 class="product-main-title">{{ $product->title }}</h2>
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
                <a class="reviewLink d-flex-center" href="#reviews"
                >یک نظر بنویسید</a
                >
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
                <span class="price old-price">{{ formatPrice($product->unit_price) }}</span><span class="price">99 هزار</span>
            </div>
            @else
            <div class="product-price d-flex-center my-3">
                <span class="price">{{ formatPrice($product->unit_price) }}</span>
            </div>
            @endif
        </div>
        <form
        method="post"
        action="#"
        class="product-form product-form-border hidedropdown"
        >
        <!-- Swatches -->
        <div class="product-swatches-option">
            <!-- Swatches Color -->
            <div
            class="product-item swatches-image w-100 mb-4 swatch-0 option1"
            data-option-index="0"
            >
            <label class="label d-flex align-items-center">رنگ ها:</label>
            <ul class="variants-clr swatches d-flex-center pt-1 clearfix">
            @foreach ($product->varieties as $variety)
                @php
                    $class = $variety->store_balance > 1 ? 'available' : ($variety->store_balance === 0 || is_null($variety->store_balance) ? 'soldout' : '');
                @endphp
                <li class="swatch x-large {{$class}} {{$loo`p->first ? 'active' : ''}}">
                <img
                    src="{{ asset('front/assets/images/products/product1-1-80x.jpg') }}"
                    alt="تصویر"
                    width="80"
                    height="80"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    {{-- title="آبی" --}}
                />
                </li>
            @endforeach
            </ul>
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
            <ul
                class="variants-size size-swatches d-flex-center pt-1 clearfix"
            >
                @foreach ($product->varieties_showcase['attributes'][0]['modelDetails'] as $item)
                @php
                    $variety = Modules\Product\Entities\Variety::find($item['myAvailableVarietyIds'][0]);
                    $class = $variety->store_balance > 1 ? 'available' : ($variety->store_balance === 0 || is_null($variety->store_balance) ? 'soldout' : '');
                @endphp
                
                <li class="swatch x-large {{ $class }}">  
                    <span  
                        class="swatchLbl"  
                        data-bs-toggle="tooltip"  
                        data-bs-placement="top"  
                        title="{{ $item['value'] }}"  
                    >{{ $item['value'] }}</span>  
                </li>  
                @endforeach
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
                    <a class="qtyBtn minus" href="#;"
                    ><i class="icon anm anm-minus-r"></i
                    ></a>
                    <input
                    type="text"
                    name="quantity"
                    value="1"
                    class="product-form-input qty"
                    />
                    <a class="qtyBtn plus" href="#;"><i class="icon anm anm-plus-r"></i></a>
                </div>
            </div>
            <!-- End Product Quantity -->
            <!-- Product Add -->
            <div class="product-form-submit addcart fl-1 me-3">
            @if($product->status == 'available')
            <button
                type="submit"
                name="add"
                class="btn btn-secondary product-form-cart-submit">
                <span>افزودن به سبد</span>
            </button>
            @else
            <button
                type="submit"
                name="add"
                class="btn btn-secondary product-form-sold-out"
                disabled="disabled"
            >
                <span>تمام شده</span>
            </button>
            @endif
            </div>
        </div>
        {{-- <p class="infolinks d-flex-center justify-content-between">
            <a class="text-link wishlist" href="wishlist-style1.html"
            ><i class="icon anm anm-heart-l ms-2"></i>
            <span>افزودن به فهرست آرزوها</span>
            </a>
        </p>
        <p class="infolinks d-flex-center justify-content-between">
            <a class="text-link compare" href="compare-style1.html"
            ><i class="icon anm anm-sync-ar ms-2"></i>
            <span>افزودن به مقایسه</span>
            </a>
        </p> --}}
        <!-- End Product Info link -->
        </form>
        <!-- End Product Form -->
        <!-- Social Sharing -->
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
        <!-- End Social Sharing -->
    </div>
    </div>
</div>
<!--Product Content-->

<!--Product Tabs-->
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

    <li id="reviews" rel="reviews"><a class="tablink">بررسی ها</a></li>
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
                <h4 class="avg-mark">5.0</h4>
                <div class="avg-content me-3">
                <p class="text-rating">میانگین امتیاز</p>
                <div class="ratings-full product-review">
                    <a class="reviewLink d-flex-center" href="#reviews"
                    ><i class="icon anm anm-star"></i
                    ><i class="icon anm anm-star"></i
                    ><i class="icon anm anm-star"></i
                    ><i class="icon anm anm-star"></i
                    ><i class="icon anm anm-star-o"></i
                    ><span class="caption me-2"
                        >24 رتبه بندی ها</span
                    ></a
                    >
                </div>
                </div>
            </div>
            </div>
            <hr />
            <div class="spr-reviews">
            <h3 class="spr-form-title">نظرات مشتریان</h3>
            <div class="review-inner">
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
                        <h5
                        class="spr-review-header-title text-transform-none mb-0"
                        >
                        النور پنا
                        </h5>
                        <span class="product-review spr-starratings m-0"
                        ><span class="reviewLink"
                            ><i class="icon anm anm-star"></i
                            ><i class="icon anm anm-star"></i
                            ><i class="icon anm anm-star"></i
                            ><i class="icon anm anm-star"></i
                            ><i class="icon anm anm-star"></i></span
                        ></span>
                    </div>
                    </div>
                    <b class="head-font">کیفیت خوب و بالا</b>
                    <p class="spr-review-body">
                    تنوع‌های زیادی از معابر در دسترس است، اما اکثریت
                    آن‌ها به نوعی با استفاده از طنز تزریقی دچار تغییر
                    شده‌اند.
                    </p>
                </div>
                </div>
                <div class="spr-review d-flex w-100">
                <div class="spr-review-profile flex-shrink-0">
                    <img
                    class="blur-up lazyload"
                    data-src="assets/images/users/testimonial1.jpg"
                    src="assets/images/users/testimonial1.jpg"
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
                        <h5
                        class="spr-review-header-title text-transform-none mb-0"
                        >
                        کورتنی هنری
                        </h5>
                        <span class="product-review spr-starratings m-0"
                        ><span class="reviewLink"
                            ><i class="icon anm anm-star"></i
                            ><i class="icon anm anm-star"></i
                            ><i class="icon anm anm-star"></i
                            ><i class="icon anm anm-star-o"></i
                            ><i class="icon anm anm-star-o"></i></span
                        ></span>
                    </div>
                    </div>
                    <b class="head-font">در دسترس بودن ویژگی</b>
                    <p class="spr-review-body">
                    قطعه استاندارد که از دهه 1500 استفاده می شد در زیر
                    برای علاقه مندان بازتولید شده است. بخش 1.10.32 و
                    1.10.33
                    </p>
                </div>
                </div>
            </div>
            </div>
        </div>

        <div class="col-12 col-sm-12 col-md-12 col-lg-6 mb-4">
            <form
            method="post"
            action="#"
            class="product-review-form new-review-form"
            >
            <h3 class="spr-form-title">نظری بنویسید</h3>
            <p>آدرس ایمیل شما منتشر نخواهد شد. فیلدهای الزامی با *</p>
            علامت گذاری شده اند
            <fieldset class="row spr-form-contact">
                <div class="col-sm-6 spr-form-contact-name form-group">
                <label class="spr-form-label" for="nickname"
                    >نام <span class="required">*</span></label
                >
                <input
                    class="spr-form-input spr-form-input-text"
                    id="nickname"
                    type="text"
                    name="name"
                    required
                />
                </div>
                <div class="col-sm-6 spr-form-contact-email form-group">
                <label class="spr-form-label" for="email"
                    >پست الکترونیک
                    <span class="required">*</span></label
                >
                <input
                    class="spr-form-input spr-form-input-email"
                    id="email"
                    type="email"
                    name="email"
                    required
                />
                </div>
                <div class="col-sm-6 spr-form-review-title form-group">
                <label class="spr-form-label" for="review"
                    >عنوان را مرور کن
                </label>
                <input
                    class="spr-form-input spr-form-input-text"
                    id="review"
                    type="text"
                    name="review"
                />
                </div>
                <div class="col-sm-6 spr-form-review-rating form-group">
                <label class="spr-form-label">رتبه بندی</label>
                <div class="product-review pt-1">
                    <div class="review-rating">
                    <a href="#;"
                        ><i class="icon anm anm-star-o"></i></a
                    ><a href="#;"
                        ><i class="icon anm anm-star-o"></i></a
                    ><a href="#;"
                        ><i class="icon anm anm-star-o"></i></a
                    ><a href="#;"
                        ><i class="icon anm anm-star-o"></i></a
                    ><a href="#;"
                        ><i class="icon anm anm-star-o"></i
                    ></a>
                    </div>
                </div>
                </div>
                <div class="col-12 spr-form-review-body form-group">
                <label class="spr-form-label" for="message"
                    >بدنه بررسی
                    <span
                    class="spr-form-review-body-charactersremaining"
                    >(1500) کاراکتر باقی مانده است</span
                    ></label
                >
                <div class="spr-form-input">
                    <textarea
                    class="spr-form-input spr-form-input-textarea"
                    id="message"
                    name="message"
                    rows="3"
                    ></textarea>
                </div>
                </div>
            </fieldset>
            <div class="spr-form-actions clearfix">
                <input
                type="submit"
                class="btn btn-primary spr-button spr-button-primary"
                value="ارسال بررسی"
                />
            </div>
            </form>
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
@endsection
@section('scripts')
    <script>
        $(document).ready(function () {
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
        });
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
