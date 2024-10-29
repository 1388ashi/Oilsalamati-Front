@extends('front.layouts.master')
@section('body_class') aboutus-page aboutus-style1-page @endsection
@section('content')
<div class="page-header mt-0 py-3">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                <div class="breadcrumbs">
                    <a href="/" title="Back to the home page">صفحه اصلی</a>
                    <span class="main-title fw-bold">
                        <i class="icon anm anm-angle-left-l"></i>
                        درباره ما
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
<section class="video-popup-section section pb-0">
    <div class="container"> 
      <div class="section-header d-none">
        <h2>فروشگاه مد هما</h2>
      </div>
      <div class="row">
        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
          <div class="video-popup-content position-relative">
            <a
              href="#aboutVideo-modal"
              class="popup-video d-flex align-items-center justify-content-center"
              data-bs-toggle="modal"
              data-bs-target="#aboutVideo_modal"
              title="مشاهده ویدئو"
            >
              <img
                class="w-100 d-block blur-up lazyload"
                data-src="{{asset('front/assets/images/about/video-popup-bg.jpg')}}"
                src="{{asset('front/assets/images/about/video-popup-bg.jpg')}}"
                alt="تصویر"
                title=""
                width="1100"
                height="600"
              />
              <i class="icon anm anm-play-cir"></i>
            </a>
          </div>
        </div>
        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
          <div class="content-section text-center col-lg-9 mx-auto mt-4">
            <h3 class="fs-4 mb-3">
              بخش 1.10.32 "صنعت چاپ و حروفچینی است. لورم ایپسوم"، نوشته
              سیسرو در 45 قبل از میلاد
            </h3>
            <p>
              لورم ایپسوم به سادگی متن ساختگی صنعت چاپ و حروفچینی است.
              لورم ایپسوم از دهه 1500 به عنوان متن ساختگی استاندارد صنعت
              بوده است، زمانی که یک چاپگر ناشناخته یک گالی از نوع را
              برداشت و آن را به هم زد تا یک کتاب نمونه بسازد. نه تنها از
              پنج قرن، بلکه از جهش به حروفچینی الکترونیکی نیز جان سالم به
              در برده است که اساساً بدون تغییر باقی مانده است. در دهه 1960
              با انتشار برگه‌های حاوی معابر لورم اپیسوم و اخیراً با
              نرم‌افزار انتشار دسکتاپ مانند از جمله نسخه‌های لورم اپیسوم
              رایج شد.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!--پایان دادن به محتوای ویدیویی-->

  <!--بخش گواهینامه-->
  <section class="section testimonial-slider style1 pb-0">
    <div class="container">
      <div class="section-header">
        <p class="mb-2 mt-0">رضایت مشتری</p>
        <h2>مشتریان ما</h2>
      </div>

      <div class="testimonial-wraper" dir="ltr">
        <!--اقلام اسلایدر گواهینامه-->
        <div
          class="testimonial-slider-3items gp15 slick-arrow-dots arwOut5"
        >
          <div class="testimonial-slide rounded-3">
            <div class="testimonial-content text-center">
              <div class="quote-icon mb-3 mb-lg-4">
                <img
                  class="blur-up lazyload mx-auto"
                  data-src="{{asset('front/assets/images/icons/demo1-quote-icon.png')}}"
                  src="{{asset('front/assets/images/icons/demo1-quote-icon.png')}}"
                  alt="آیکن"
                  width="40"
                  height="40"
                />
              </div>
              <div class="content">
                <div class="text mb-2">
                  <p>
                    لورم ایپسوم به سادگی متن ساختگی صنعت چاپ و حروفچینی
                    است. لورم اپیسوم متن ساختگی استاندارد صنعت 1500 بوده
                    است.
                  </p>
                </div>
                <div class="product-review my-3">
                  <i class="icon anm anm-star"></i
                  ><i class="icon anm anm-star"></i
                  ><i class="icon anm anm-star"></i
                  ><i class="icon anm anm-star"></i
                  ><i class="icon anm anm-star"></i>
                  <span class="caption hidden ms-1">24 نظر</span>
                </div>
              </div>
              <div class="auhimg d-flex-justify-center text-right">
                <div class="image">
                  <img
                    class="rounded-circle blur-up lazyload"
                    data-src="{{asset('front/assets/images/users/testimonial1-65x.jpg')}}"
                    src="{{asset('front/assets/images/users/testimonial1-65x.jpg')}}"
                    alt="نقل قول"
                    width="65"
                    height="65"
                  />
                </div>
                <div class="auhtext me-3">
                  <h5 class="authour mb-1">جان دو</h5>
                  <p class="text-muted">بنیانگذار و مدیر عامل</p>
                </div>
              </div>
            </div>
          </div>
          <div class="testimonial-slide rounded-3">
            <div class="testimonial-content text-center">
              <div class="quote-icon mb-3 mb-lg-4">
                <img
                  class="blur-up lazyload mx-auto"
                  data-src="{{asset('front/assets/images/icons/demo1-quote-icon.png')}}"
                  src="{{asset('front/assets/images/icons/demo1-quote-icon.png')}}"
                  alt="آیکن"
                  width="40"
                  height="40"
                />
              </div>
              <div class="content">
                <div class="text mb-2">
                  <p>
                    لورم ایپسوم به سادگی متن ساختگی صنعت چاپ و حروفچینی
                    است. لورم اپیسوم متن ساختگی استاندارد صنعت 1500 بوده
                    است.
                  </p>
                </div>
                <div class="product-review my-3">
                  <i class="icon anm anm-star"></i
                  ><i class="icon anm anm-star"></i
                  ><i class="icon anm anm-star"></i
                  ><i class="icon anm anm-star"></i
                  ><i class="icon anm anm-star-o"></i>
                  <span class="caption hidden ms-1">15 نظر</span>
                </div>
              </div>
              <div class="auhimg d-flex-justify-center text-right">
                <div class="image">
                  <img
                    class="rounded-circle blur-up lazyload"
                    data-src="{{asset('front/assets/images/users/testimonial2-65x.jpg')}}"
                    src="{{asset('front/assets/images/users/testimonial2-65x.jpg')}}"
                    alt="نقل قول"
                    width="65"
                    height="65"
                  />
                </div>
                <div class="auhtext me-3">
                  <h5 class="authour mb-1">جسیکا دو</h5>
                  <p class="text-muted">بازاریابی</p>
                </div>
              </div>
            </div>
          </div>
          <div class="testimonial-slide rounded-3">
            <div class="testimonial-content text-center">
              <div class="quote-icon mb-3 mb-lg-4">
                <img
                  class="blur-up lazyload mx-auto"
                  data-src="{{asset('front/assets/images/icons/demo1-quote-icon.png')}}"
                  src="{{asset('front/assets/images/icons/demo1-quote-icon.png')}}"
                  alt="آیکن"
                  width="40"
                  height="40"
                />
              </div>
              <div class="content">
                <div class="text mb-2">
                  <p>
                    لورم ایپسوم به سادگی متن ساختگی صنعت چاپ و حروفچینی
                    است. لورم اپیسوم متن ساختگی استاندارد صنعت 1500 بوده
                    است.
                  </p>
                </div>
                <div class="product-review my-3">
                  <i class="icon anm anm-star"></i
                  ><i class="icon anm anm-star"></i
                  ><i class="icon anm anm-star"></i
                  ><i class="icon anm anm-star-o"></i
                  ><i class="icon anm anm-star-o"></i>
                  <span class="caption hidden ms-1">17 نظر</span>
                </div>
              </div>
              <div class="auhimg d-flex-justify-center text-right">
                <div class="image">
                  <img
                    class="rounded-circle blur-up lazyload"
                    data-src="{{asset('front/assets/images/users/testimonial3-65x.jpg')}}"
                    src="{{asset('front/assets/images/users/testimonial3-65x.jpg')}}"
                    alt="نقل قول"
                    width="65"
                    height="65"
                  />
                </div>
                <div class="auhtext me-3">
                  <h5 class="authour mb-1">ریک ادوارد</h5>
                  <p class="text-muted">برنامه‌نویس</p>
                </div>
              </div>
            </div>
          </div>
          <div class="testimonial-slide rounded-3">
            <div class="testimonial-content text-center">
              <div class="quote-icon mb-3 mb-lg-4">
                <img
                  class="blur-up lazyload mx-auto"
                  data-src="{{asset('front/assets/images/icons/demo1-quote-icon.png')}}"
                  src="{{asset('front/assets/images/icons/demo1-quote-icon.png')}}"
                  alt="آیکن"
                  width="40"
                  height="40"
                />
              </div>
              <div class="content">
                <div class="text mb-2">
                  <p>
                    لورم ایپسوم به سادگی متن ساختگی صنعت چاپ و حروفچینی
                    است. لورم اپیسوم متن ساختگی استاندارد صنعت 1500 بوده
                    است.
                  </p>
                </div>
                <div class="product-review my-3">
                  <i class="icon anm anm-star"></i
                  ><i class="icon anm anm-star"></i
                  ><i class="icon anm anm-star-o"></i>
                  <i class="icon anm anm-star-o"></i
                  ><i class="icon anm anm-star-o"></i>
                  <span class="caption hidden ms-1">29 نظر</span>
                </div>
              </div>
              <div class="auhimg d-flex-justify-center text-right">
                <div class="image">
                  <img
                    class="rounded-circle blur-up lazyload"
                    data-src="{{asset('front/assets/images/users/testimonial4-65x.jpg')}}"
                    src="{{asset('front/assets/images/users/testimonial4-65x.jpg')}}"
                    alt="نقل قول"
                    width="65"
                    height="65"
                  />
                </div>
                <div class="auhtext me-3">
                  <h5 class="authour mb-1">خریدار خوشحال</h5>
                  <p class="text-muted">طراح</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!--اقلام اسلایدر گواهینامه-->
      </div>
    </div>
  </section>
  @endsection