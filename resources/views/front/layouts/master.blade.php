<!DOCTYPE html>
<html lang="fa" dir="rtl">
	<head>
		
		@include('front.layouts.includes.meta-tags')
		@include("front.layouts.includes.styles")

		<title>@yield('title', config('app.name'))</title>

		@yield("styles")

	</head>

	<body class="template-product product-layout1">
    <div class="page-wrapper">
      
			@include('front.layouts.includes.header')
			@include('front.layouts.includes.mobile-menu')
			
      <div id="page-content">
        <div class="page-header text-center mt-0">
          <div class="container">
            <div class="row align-items-center">
              <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                @yield("breadcrumb")
              </div>
            </div>
          </div>
        </div>
        @yield('content')
      </div>
      
			@include('front.layouts.includes.footer')
			@include('front.layouts.includes.mini-cart')
			@include('front.layouts.includes.scripts')

			@yield('scripts')

    </div>
  </body>
</html>
