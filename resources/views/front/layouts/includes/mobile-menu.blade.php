<div class="mobile-nav-wrapper" role="navigation">
  <div class="closemobileMenu">
    بستن منو <i class="icon anm anm-times-l"></i>
  </div>
  <ul id="MobileNav" class="mobile-nav">
    <li><a href="/">صفحه اصلی </a></li>
    <li class="lvl1 parent dropdown">
      <a>دسته بندی محصولات <i class="icon anm anm-angle-down-l"></i></a>
      <ul class="dropdown">
        @foreach ($categories as $category)
          <li>
            <a href="{{ route('front.products.index', ['category_id' => $category['id']]) }}" class="site-nav">
              {{ $category['title'] }}
              @if ($category['children'])
                <i class="icon anm anm-angle-left-l"></i>
              @endif
            </a>
            @if ($category['children'])
              <ul class="dropdown">
                @foreach ($category['children'] as $category)
                  <li>
                    <a href="{{ route('front.products.index', ['category_id' => $category['id']]) }}" class="site-nav">{{ $category['title'] }}</a>
                  </li>
                @endforeach
              </ul>
            @endif
          </li>
        @endforeach
      </ul>
    </li>
    <li><a href="{{ route('posts.index') }}">وبلاگ </a></li>
    <li><a  href="{{route('about-us')}}">درباره ما </a></li>
    <li><a href="{{route('contacts.index')}}">تماس ما </a></li>
  </ul>
</div>