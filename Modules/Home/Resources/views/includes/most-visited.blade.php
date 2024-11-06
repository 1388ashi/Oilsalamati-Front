<section class="section product-slider pb-0">
	<div class="container">
		<div class="section-header">
			<h2>پر بازدید ترین ها</h2>
		</div>

		<div class="grid-products product-slider-4items gp15 arwOut5 hov-arrow" dir="ltr">

			@foreach ($response['mostVisited'] as $product)
			<div class="item col-item">
				<div class="product-box">
					<div class="product-image">
						<a href="{{ route('products.show', $product->id) }}" class="product-img">

							<img
								class="primary blur-up lazyload"
								data-src="{{asset($product->images_showcase['main_image']->url)}}"
								src="{{asset($product->images_showcase['main_image']->url)}}"
								alt="{{ $product->title }}"
								title="{{ $product->title }}"
								width="625"
								height="703"
							/>

							<img
								class="hover blur-up lazyload"
								data-src="{{asset($product->images_showcase['main_image']->url)}}"
								src="{{asset($product->images_showcase['main_image']->url)}}"
								alt="{{ $product->title }}"
								title="{{ $product->title }}"
								width="625"
								height="703"
							/>

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

					<div class="product-details text-center">

						<div class="product-name">
							<a href="{{ route('products.show', $product->id) }}">{{ Str::limit($product->title, 45) }}</a>
						</div>

						<div class="product-price">
							@if ($hasDiscount)
								<span class="price old-price">{{ number_format($finalPrice['base_amount']) }} تومان </span>
							@endif
							<span class="price">{{ number_format($finalPrice['amount']) }} تومان </span>
						</div>
						
					</div>

				</div>
			</div>
			@endforeach

		</div>

		<div class="view-collection text-center mt-4 mt-md-5 mb-md-5">
			<a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-lg">مشاهده همه</a>
		</div>

	</div>
</section>