@extends('front.layouts.master')

@section('title')
	<title>لیست محصولات</title>
@endsection

@section('body_class') shop-page sidebar-filter shop-grid-view-page @endsection

@section('content')

<div class="page-header mt-0 py-3">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-12 col-sm-12 col-md-12 col-lg-12">
				<div class="breadcrumbs">
					<a href="/" title="Back to the home page">صفحه اصلی</a>
					<span class="main-title fw-bold">
						<i class="icon anm anm-angle-left-l"></i>
						محصولات
					</span>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="container">

	@if ($response['childCategories']?->isNotEmpty())
		<div class="collection-slider-6items gp10 slick-arrow-dots sub-collection section pt-0" dir="ltr">
			@foreach ($response['childCategories'] as $category)
				<div class="category-item zoomscal-hov">
					<a href="{{ route('products.index', ['category_id' => $category->id]) }}" class="category-link clr-none">
						<div class="zoom-scal zoom-scal-nopb rounded-0">
							<img
								class="rounded-0 blur-up lazyload"
								data-src="{{ asset($category->image->url) }}"
								src="{{ asset($category->image->url) }}"
								alt="{{ $category->title }}"
								title="{{ $category->title }}"
								width="365"
								height="365"
							/>
						</div>
						<div class="details text-center">
							<h4 class="category-title mb-0">{{ $category->title }}</h4>
							<p class="counts">{{ $category->products_count }} محصول</p>
						</div>
					</a>
				</div>
			@endforeach
		</div>
	@endif

	<!--Toolbar-->
	<div class="toolbar toolbar-wrapper shop-toolbar">
		<div class="row align-items-center">
			<div class="col-4 col-sm-2 col-md-4 col-lg-4 text-left filters-toolbar-item d-flex order-1 order-sm-0">
				<button type="button" class="btn btn-filter text icon anm anm-sliders-hr d-inline-flex ms-2 me-lg-3" >فیلتر کنید</button>
				<div class="filters-item d-flex align-items-center">
					<label class="mb-0 me-2 d-none d-lg-inline-block">نمایش به صورت:</label>
					<div class="grid-options view-mode d-flex" style="margin-right: 10px;">
						<a class="icon-mode mode-grid grid-2 d-block" data-col="2"></a>
						<a class="icon-mode mode-grid grid-3 d-md-block" data-col="3"></a>
						<a class="icon-mode mode-grid grid-4 d-lg-block" data-col="4"></a>
						<a class="icon-mode mode-grid grid-5 d-xl-block" data-col="5"></a>
					</div>
				</div>
			</div>
			<div class="col-12 col-sm-4 col-md-4 col-lg-4 text-center product-count order-0 order-md-1 mb-3 mb-sm-0"></div>
			<div class="col-8 col-sm-6 col-md-4 col-lg-4 text-right filters-toolbar-item d-flex justify-content-end order-2 order-sm-2">
				<div class="filters-item d-flex align-items-center">
					<label for="ShowBy" class="mb-0 ms-2 text-nowrap d-none d-sm-inline-flex">نمایش دهید:</label>
				</div>
				<div class="filters-item d-flex align-items-center ms-2 ms-lg-3">
					<label for="SortBy" class="mb-0 me-2 text-nowrap d-none">مرتب سازی بر اساس:</label>
					<select id="SortBy" class="filters-toolbar-sort" onchange="sortProducts(event)">
						<option value="">انتخاب</option>
						<option value="mostVisited" @if (request('sortBy') == 'mostVisited') selected @endif>پربازدید ترین</option>
						<option value="mostSales" @if (request('sortBy') == 'mostSales') selected @endif>پرفروش ترین</option>
						<option value="mostDiscount" @if (request('sortBy') == 'mostDiscount') selected @endif>پرتخفیف ترین</option>
					</select>
				</div>
			</div>
		</div>
	</div>
	<!--End Toolbar-->

	<div class="row">
		<!--Sidebar-->
		<div class="col-12 col-sm-12 col-md-12 col-lg-3 sidebar sidebar-bg filterbar">
			<div class="closeFilter">
				<i class="icon anm anm-times-r"></i>
			</div>
			<div class="sidebar-tags clearfix">
				<div class="sidebar-widget clearfix categories filterBox filter-widget">
					<div class="widget-title"><h2>دسته بندی ها</h2></div>
					<div class="widget-content filterDD">
						<ul class="sidebar-categories scrollspy morelist clearfix">

							@php
								$route = 'front.products.index';
								$param = 'category_id';
							@endphp

							@foreach ($response['categories'] as $category)
								@if ($category->children?->isNotEmpty())
									<li class="lvl1 {{ $category->children?->isNotEmpty() ? 'sub-level' : ''}}">
										<a class="site-nav">{{ $category->title }}</a>
										<ul class="sublinks">
											@foreach ($category->children as $category)
												<li class="lvl2">
													<a class="site-nav" href="{{ route($route, [$param => $category->id]) }}">{{ $category->title }}</a>
												</li>
											@endforeach
										</ul>
									</li>
								@else
									<li class="lvl1 more-item">
										<a href="{{ route($route, [$param => $category->id]) }}" class="site-nav">{{ $category->title }}</a>
									</li>
								@endif
							@endforeach
						</ul>
					</div>
				</div>
				<div class="sidebar-widget filterBox filter-widget">
					<div class="widget-title"><h2>قیمت</h2></div>
					<form id="FilterForm" class="widget-content price-filter filterDD" action="{{ route('products.index') }}">
						<input type="hidden" name="color_id">
						<input type="hidden" name="sortBy">
						<div id="slider-range" class="mt-2"></div>
						<div class="row">
							<div class="col-6"><input id="amount" type="text"/></div>
							<div class="col-6 text-right">
								<button class="btn btn-sm">فیلتر کنید</button>
							</div>
						</div>
					</form>
				</div>
				<div class="sidebar-widget filterBox filter-widget">
					<div class="widget-title"><h2>رنگ</h2></div>
					<div class="widget-content filter-color filterDD">
						<ul class="swacth-list swatches d-flex-center clearfix pt-0">
							@foreach ($response['colors'] as $color)
								<li class="swatch large radius available">
									<span
										style="background-color: {{ $color['code'] }}; width:28px; height: 28px;"
										onclick="filterProductsByColor(@json($color['id']))"
										data-bs-toggle="tooltip"
										data-bs-placement="top"
										title="{{ $color['name'] }}"
									/>
								</li>
							@endforeach
						</ul>
					</div>
				</div>
				{{-- <div class="sidebar-widget filterBox filter-widget">
					<div class="widget-title"><h2>اندازه</h2></div>
					<div class="widget-content filter-size filterDD">
						<ul
							class="swacth-list size-swatches d-flex-center clearfix"
						>
							<li class="swatch large radius soldout">
								<span
									class="swatchLbl"
									data-bs-toggle="tooltip"
									data-bs-placement="top"
									title="XS"
									>XS</span
								>
							</li>
							<li class="swatch large radius available">
								<span
									class="swatchLbl"
									data-bs-toggle="tooltip"
									data-bs-placement="top"
									title="S"
									>S</span
								>
							</li>
							<li class="swatch large radius available">
								<span
									class="swatchLbl"
									data-bs-toggle="tooltip"
									data-bs-placement="top"
									title="M"
									>M</span
								>
							</li>
							<li class="swatch large radius available">
								<span
									class="swatchLbl"
									data-bs-toggle="tooltip"
									data-bs-placement="top"
									title="L"
									>L</span
								>
							</li>
							<li class="swatch large radius available">
								<span
									class="swatchLbl"
									data-bs-toggle="tooltip"
									data-bs-placement="top"
									title="X"
									>X</span
								>
							</li>
							<li class="swatch large radius available active">
								<span
									class="swatchLbl"
									data-bs-toggle="tooltip"
									data-bs-placement="top"
									title="XL"
									>XL</span
								>
							</li>
							<li class="swatch large radius available">
								<span
									class="swatchLbl"
									data-bs-toggle="tooltip"
									data-bs-placement="top"
									title="XLL"
									>XLL</span
								>
							</li>
							<li class="swatch large radius available">
								<span
									class="swatchLbl"
									data-bs-toggle="tooltip"
									data-bs-placement="top"
									title="XXL"
									>XXL</span
								>
							</li>
							<li class="swatch large radius available">
								<span
									class="swatchLbl"
									data-bs-toggle="tooltip"
									data-bs-placement="top"
									title="25"
									>25</span
								>
							</li>
							<li class="swatch large radius available">
								<span
									class="swatchLbl"
									data-bs-toggle="tooltip"
									data-bs-placement="top"
									title="35"
									>35</span
								>
							</li>
							<li class="swatch large radius available">
								<span
									class="swatchLbl"
									data-bs-toggle="tooltip"
									data-bs-placement="top"
									title="40"
									>40</span
								>
							</li>
						</ul>
					</div>
				</div> --}}
			</div>
		</div>

		<div class="col-12 col-sm-12 col-md-12 col-lg-12 main-col">
			<!--Product Grid-->
			<div class="grid-products grid-view-items">
				<div class="row col-row product-options row-cols-xl-5 row-cols-lg-4 row-cols-md-3 row-cols-sm-3 row-cols-2">
					@foreach ($response['products'] as $product)
					<div class="item col-item">
						<div class="product-box">
							<div class="product-image">
								<a href="{{ route('products.show', $product) }}" class="product-img rounded-0" >
									<img
										class="rounded-0 blur-up lazyload"
										src="{{ asset($product->images_showcase['main_image']?->url) }}"
										alt="{{ $product->title }}"
										title="{{ $product->title }}"
										width="625"
										height="808"
									/>
{{--                                    <img--}}
{{--                                        class="rounded-0 blur-up lazyload"--}}
{{--                                        src="{{asset('image/image.jpg')}}"--}}
{{--                                        alt="{{ $product->title }}"--}}
{{--                                        title="{{ $product->title }}"--}}
{{--                                        width="625"--}}
{{--                                        height="808"--}}
{{--                                    />--}}
								</a>

								@php
									$finalPrice = $product->final_price;
									$hasDiscount = $finalPrice['discount_price'] > 0 ? true : false;
								@endphp

								@if ($hasDiscount)
									<div class="product-labels">
										@if ($finalPrice['discount_type'] === 'percentage')
											<span class="lbl on-sale">{{ $finalPrice['discount'] . '%' }} تخفیف</span>
										@else
											<span class="lbl on-sale">{{ number_format($finalPrice['discount_price']) . ' تومان' }} تخفیف</span>
										@endif
									</div>
								@endif

							</div>
							<div class="product-details text-right">
								<div class="product-name">
									<a href="{{ route('products.show', $product) }}">{{ $product->title }}</a>
								</div>
								<div class="product-price">
									@if ($hasDiscount)
										<span class="price old-price">{{ number_format($finalPrice['base_amount']) }} تومان </span>
									@endif
									<span class="price">{{ number_format($finalPrice['amount']) }} تومان </span>
								</div>
								{{-- <ul class="variants-clr swatches">
									<li class="swatch medium radius">
										<span
											class="swatchLbl"
											data-bs-toggle="tooltip"
											data-bs-placement="top"
											title="دریایی"
											><img
												src="assets/images/products/product1.jpg"
												alt="تصویر"
												width="625"
												height="808"
										/></span>
									</li>
									<li class="swatch medium radius">
										<span
											class="swatchLbl"
											data-bs-toggle="tooltip"
											data-bs-placement="top"
											title="سبز"
											><img
												src="assets/images/products/product1-1.jpg"
												alt="تصویر"
												width="625"
												height="808"
										/></span>
									</li>
									<li class="swatch medium radius">
										<span
											class="swatchLbl"
											data-bs-toggle="tooltip"
											data-bs-placement="top"
											title="خاکستری"
											><img
												src="assets/images/products/product1-2.jpg"
												alt="تصویر"
												width="625"
												height="808"
										/></span>
									</li>
									<li class="swatch medium radius">
										<span
											class="swatchLbl"
											data-bs-toggle="tooltip"
											data-bs-placement="top"
											title="نارنجی"
											><img
												src="assets/images/products/product1-3.jpg"
												alt="تصویر"
												width="625"
												height="808"
										/></span>
									</li>
								</ul> --}}
							</div>
						</div>
					</div>
					@endforeach
				</div>

			</div>
		</div>
	</div>
</div>

@endsection

@section('scripts')
	<script>

		function sortProducts(event) {
			$('#FilterForm').find('input[name="sortBy"]').val(event.target.value);
			$('#FilterForm').submit()
		}

		function filterProductsByColor(colorId) {
			$('#FilterForm').find('input[name="color_id"]').val(colorId);
			$('#FilterForm').submit()
		}

	</script>
@endsection
