<div class="top-header">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-6 col-sm-6 col-md-3 col-lg-4 text-right">
        <i class="icon anm anm-phone-l ms-2"></i><a href="tel:401234567890" dir="ltr">{{ $settings['site']['mobile_support'] }}</a>
      </div>
      <div class="col-12 col-sm-12 col-md-6 col-lg-4 text-center d-none d-md-block">
        ارسال رایگان برای همه سفارش‌های بالای 99 تومان
        <a href="#" class="text-link me-1">اکنون خرید کنید</a>
      </div>
      <div class="col-6 col-sm-6 col-md-3 col-lg-4 text-left d-flex align-items-center justify-content-end">
        <div class="social-email left-brd d-inline-flex">
          <ul class="list-inline social-icons d-inline-flex align-items-center">
            @if ($settings['social']['facebook'])
              <li class="list-inline-item">
                <a href="{{ $settings['social']['facebook'] }}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="فیسبوک" ><i class="icon anm anm-facebook-f"></i></a>
              </li>
            @endif
            @if ($settings['social']['twitter'])
              <li class="list-inline-item">
                <a href="{{ $settings['social']['twitter'] }}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="توییتر" ><i class="icon anm anm-facebook-f"></i></a>
              </li>
            @endif
            @if ($settings['social']['linkedin'])
              <li class="list-inline-item">
                <a href="{{ $settings['social']['linkedin'] }}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="لینکدین" ><i class="icon anm anm-facebook-f"></i></a>
              </li>
            @endif
            @if ($settings['social']['instagram'])
              <li class="list-inline-item">
                <a href="{{ $settings['social']['instagram'] }}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="اینستاگرام" ><i class="icon anm anm-facebook-f"></i></a>
              </li>
            @endif
            @if ($settings['social']['youtube'])
              <li class="list-inline-item">
                <a href="{{ $settings['social']['youtube'] }}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="یوتوب" ><i class="icon anm anm-facebook-f"></i></a>
              </li>
            @endif
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<!--Header-->
<header class="header d-flex align-items-center header-1 header-fixed">
  <div class="container">
    <div class="row">
      <!--Logo-->
      <div class="logo col-5 col-sm-3 col-md-3 col-lg-2 align-self-center">
        <a class="logoImg" href="/">
          <img
            src="{{asset('front/assets/images/logo/logo.9208f443.svg')}}"
            alt="قالب  چند منظوره هما"
            title="قالب  چند منظوره هما"
            width="149"
            height="39"
          />
        </a>
      </div>
      <!--پایان لوگو-->
      <!--منو-->
      <div class="col-1 col-sm-1 col-md-1 col-lg-8 align-self-center d-menu-col">
        <nav class="navigation" id="AccessibleNav">
          <ul id="siteNav" class="site-nav medium center">
            <li><a href="/">صفحه اصلی </a></li>
            <li class="lvl1 parent dropdown">
              <a href="{{ route('products.index') }}">دسته بندی محصولات <i class="icon anm anm-angle-down-l"></i></a>
              <ul class="dropdown">
                @foreach ($categories as $category)
                  <li>
                    <a href="{{ route('products.index', ['category_id' => $category['id']]) }}" class="site-nav">
                      {{ $category['title'] }}
                      @if ($category['children'])
                        <i class="icon anm anm-angle-left-l"></i>
                      @endif
                    </a>
                    @if ($category['children'])
                      <ul class="dropdown">
                        @foreach ($category['children'] as $category)
                          <li>
                            <a href="{{ route('products.index', ['category_id' => $category['id']]) }}" class="site-nav">{{ $category['title'] }}</a>
                          </li>
                        @endforeach
                      </ul>
                    @endif
                  </li>
                @endforeach
              </ul>
            </li>
            @foreach ($menu['header'] as $menu)
              <li><a href="{{ $menu['link'] }}">{{ $menu['title'] }}</a></li>
            @endforeach
          </ul>
        </nav>
      </div>
      <!--End Menu-->
      <!--نماد سمت راست-->
      <div class="col-7 col-sm-9 col-md-9 col-lg-2 align-self-center icons-col text-left">
        <!--جستجو-->
        <div class="search-parent iconset">
          <div class="site-search" title="جستجو">
            <a class="search-icon clr-none" data-bs-toggle="offcanvas" data-bs-target="#search-drawer" ><i class="hdr-icon icon anm anm-search-l"></i></a>
          </div>
          <div class="search-drawer offcanvas offcanvas-top" tabindex="-1" id="search-drawer">
            <div class="container">
              <div class="search-header d-flex-center justify-content-between mb-3">
                <h3 class="title m-0">به دنبال چه هستید؟</h3>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close" ></button>
              </div>
              <div class="search-body">
                <div class="form minisearch" id="header-search">
                  <!--فیلد جستجو-->
                  <div class="d-flex searchField">
                    <div class="search-category"></div>
                    <div class="input-box d-flex fl-1">
                      <input type="text" class="input-text" placeholder="جستجوی محصولات..." id="SearchProductInput"/>
                      <button type="button" class="action  d-flex-justify-center btn rounded-start-0">
                        <i class="icon anm anm-search-l"></i>
                      </button>
                    </div>
                  </div>
                  <!--پایان فیلد جستجو-->

                  <li class="item d-none" id="ProductSearchItem">
                    <div class="mini-list-item d-flex align-items-center w-100 clearfix">
                      <div class="mini-image text-center">
                        <a class="item-link">
                          <img class="item-image blur-up lazyload" width="120" height="170"/>
                        </a>
                      </div>
                      <div class="me-3 details text-right">
                        <div class="product-name">
                          <a class="item-title"></a>
                        </div>
                        <div class="product-price">
                          <span class="old-price"></span>
                          <span class="price"></span>
                        </div>
                        <div class="product-review d-flex align-items-center justify-content-start"></div>
                      </div>
                    </div>
                  </li>

                  <!--جستجوی محصولات-->
                  <div class="search-products">
                    <ul class="items g-2 g-md-3 row row-cols-lg-4 row-cols-md-3 row-cols-sm-2" id="ProductSearchBox">
                      <li class="item vala w-100 text-center text-muted d-none">شما هیچ موردی در جستجوی خود ندارید.</li>
                    </ul>
                  </div>
                  <!--پایان جستجوی محصولات-->
                </div>
              </div>
            </div>
          </div>
        </div>
        <!--پایان جستجو-->
        <div class="header-cart iconset" title="سبدخرید">
          <a href="#;" class="header-cart btn-minicart clr-none" data-bs-toggle="offcanvas" data-bs-target="#minicart-drawer">
            <i class="hdr-icon icon anm anm-cart-l"></i>
            <span id="num-cart-count" class="cart-count">0</span>
          </a>
        </div>
        <div class="account-parent iconset">
          @if (auth()->guard('customer')->check())
            <div class="account-link" title="حساب" style="background: #2f415d; padding: 8px 24px; border-radius: 20px;">
              <a href="{{ route('customer.my-account') }}" class="text-light d-flex gap-2 align-items-center">
                @php($customer = auth()->guard('customer')->user())
                <i class="hdr-icon icon anm anm-user-al text-light"></i>
                <span>{{ $customer->full_name ?? $customer->mobile }}</span> 
              </a>
            </div>
          @else
            <div class="account-link" title="حساب">
              <a href="{{ route('customer.my-account') }}">
                <i class="hdr-icon icon anm anm-user-al"></i>
              </a>
            </div>
          @endif
        </div>
        {{-- <div class="wishlist-link iconset" title="علاقه مندی">
          <a href="wishlist-style1.html"><i class="hdr-icon icon anm anm-heart-l"></i><span class="wishlist-count">0</span></a>
        </div> --}}
        <button type="button" class="iconset pe-0 menu-icon js-mobile-nav-toggle mobile-nav--open d-lg-none" title="منو">
          <i class="hdr-icon icon anm anm-times-l"></i>
          <i class="hdr-icon icon anm anm-bars-r"></i>
        </button>
      </div>
    </div>
  </div>
</header>