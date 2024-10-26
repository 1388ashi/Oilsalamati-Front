<!DOCTYPE html>
<html class="no-js" lang="en">
	<head>
		
		@include('front.layouts.includes.meta-tags')
		@yield('title')
		@include("front.layouts.includes.styles")
		@yield("styles")

	</head>

	<body class="template-index index-demo1">
    <div class="page-wrapper">
      
			@include('front.layouts.includes.header')
			@include('front.layouts.includes.mobile-menu')
			
      <div id="page-content">
        @yield('content')
      </div>
      
			@include('front.layouts.includes.footer')
			@include('front.layouts.includes.mini-cart')
			@include('front.layouts.includes.scripts')

			@yield('scripts')

    </div>
  </body>
</html>
