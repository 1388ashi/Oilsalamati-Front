<!DOCTYPE html>
<html class="no-js" lang="en">
	<head>
		
		@include('front.layouts.includes.meta-tags')
		@yield('title')
		@include("front.layouts.includes.styles")
		@yield("styles")

	</head>

	<body class="@yield('body_class')">
    <div class="page-wrapper">
      
			@include('front.layouts.includes.header')
			@include('front.layouts.includes.mobile-menu')
			
      <div id="page-content">
        @yield('content')
      </div>
			
    </div>

		<div id="loader-div">
			<div id="loader" class="loader"></div>
		</div>
		

		@include('front.layouts.includes.footer')
		@include('front.layouts.includes.mini-cart')
		@include('front.layouts.includes.scripts')
		@include('product::front.minicart-scripts')

		@yield('scripts')

  </body>
</html>
