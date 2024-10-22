<!DOCTYPE html>
<html lang="fa" dir="rtl">
	<head>
		<meta charset="UTF-8">
		<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
		<meta content="DayOne - It is one of the Major Dashboard Template which includes - HR, Employee and Job Dashboard. This template has multipurpose HTML template and also deals with Task, Project, Client and Support System Dashboard." name="description">
		<meta content="Spruko Technologies Private Limited" name="author">
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<meta name="keywords" content="admin dashboard, admin panel template, html admin template, dashboard html template, bootstrap 4 dashboard, template admin bootstrap 4, simple admin panel template, simple dashboard html template,  bootstrap admin panel, task dashboard, job dashboard, bootstrap admin panel, dashboards html, panel in html, bootstrap 4 dashboard"/>

		<title>@yield('title', config('app.name'))</title>

		@include("admin.layouts.includes.styles")
		@yield("styles")
        @stack("range-style")
        @yield("header_script")

	</head>

	<body class="app sidebar-mini">

		<div id="global-loader" >
			<img src="{{asset('assets/images/svgs/loader.svg')}}" alt="loader">
		</div>

		<div class="page">
			<div class="page-main">

				@include("admin.layouts.includes.sidebar")
				@include("admin.layouts.includes.header")

				<div class="app-content" style="padding-left: 50px; padding-right: 50px;">

					@yield("content")

				</div>
			</div>

		</div>
		<a href="#" id="back-to-top"><span class="feather feather-chevrons-up"></span></a>

		@include("admin.layouts.includes.scripts")

		@yield("scripts")
        @stack("range-script")
	</body>
</html>
