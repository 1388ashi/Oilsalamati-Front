<section class="section collection-slider pb-0">
	<div class="container">
		<div class="collection-slider-5items gp15 arwOut5 hov-arrow" dir="ltr">
			@foreach ($response['specialCategories'] as $category)
				<div class="category-item zoomscal-hov">
					<a href="#" class="category-link clr-none">
						<div class="zoom-scal zoom-scal-nopb rounded-3">
							<img
								class="blur-up lazyload"
								data-src="{{ asset($category->image->url) }}"
								src="{{ asset($category->image->url) }}"
								alt="{{ $category->title }}"
								title="{{ $category->title }}"
								style="min-height: 230px;"
								width="365"
								height="365"
							/>
						</div>
						<div class="details mt-3 text-center">
							<h4 class="category-title mb-0">{{ $category->title }}</h4>
							<p class="counts">{{ $category->products_count }} محصول</p>
						</div>
					</a>
				</div>
			@endforeach
		</div>
	</div>
</section>