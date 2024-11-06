@extends('front.layouts.master')

@section('title')
	<title>اطلاعات حساب کاربری</title>
@endsection

@section('body_class') account-page my-account-page @endsection
 
@section('content')

<div class="page-header mt-0 py-2">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-12 col-sm-12 col-md-12 col-lg-12">
				<div class="breadcrumbs">
					<a href="/" title="Back to the home page">صفحه اصلی</a>
					<span class="main-title fw-bold">
						<i class="icon anm anm-angle-left-l"></i>
						حساب من
					</span>
				</div>
			</div>
		</div>
	</div>
</div>

<x-alert-danger/>

<div class="container">
  <div class="row">
    <div class="col-12 col-sm-12 col-md-12 col-lg-3 mb-4 mb-lg-0">
      <div class="dashboard-sidebar bg-block">
        <div class="profile-top text-center mb-4 px-3">
          <div class="profile-image mb-3">
            <img class="rounded-circle blur-up lazyload" src="{{ asset('front/assets/images/users/user-img2.jpg') }}" alt="user" width="130"/>
          </div>
          <div class="profile-detail">
            <h3 class="mb-1">{{ $customer->full_name }}</h3>
            <p class="text-muted">موجودی کیف پول :<b class="text-dark wallet-balance" data-balance="{{ $customer->balance }}">{{ number_format($customer->balance) }} تومان</b></p>
          </div>
        </div>
        @include('customer::front.includes.tabs.nav-tabs')
      </div>
    </div>
    <div class="col-12 col-sm-12 col-md-12 col-lg-9">
      <div class="dashboard-content tab-content h-100" id="top-tabContent">
        @include('customer::front.includes.tabs.info')
        @include('customer::front.includes.tabs.address')
        @include('customer::front.includes.tabs.orders')
        @include('customer::front.includes.tabs.favorites')
        @include('customer::front.includes.tabs.wallet')
      </div>
    </div>
  </div>
</div>

@include('customer::front.includes.examples.address-box')

@endsection

@section('scripts')
  @include('customer::front.includes.scripts.addresses')
  @include('customer::front.includes.scripts.wallet')
  @include('customer::front.includes.scripts.info')
  @include('customer::front.includes.scripts.favorites')
@endsection