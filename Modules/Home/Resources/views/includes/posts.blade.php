<section class="section home-blog-post">
	<div class="container">

		<div class="section-header">
			<p class="mb-2 mt-0">آخرین پست</p>
			<h2>اخرین اخبار</h2>
		</div>

		<div class="blog-slider-3items gp15 arwOut5 hov-arrow" dir="ltr">

			@foreach ($response['post'] as $post)
				<div class="blog-item">
					<div class="blog-article zoomscal-hov">
						<div class="blog-img">

							<a class="featured-image zoom-scal" href="#">
								<img
									class="blur-up lazyload"
									data-src="{{ asset($post->image->url) }}"
									src="{{ asset($post->image->url) }}"
									alt="{{ $post->title }}"
									width="740"
									height="410"
								/>
							</a>

							@php
								$date = verta($post->published_at);
							@endphp

							<div class="date">
								<span class="dt">{{ $date->day }}</span>
								<span class="mt">{{ $date->copy()->format('%B') }}<br/>​​<b>{{ $date->copy()->format('Y') }}</b></span>
							</div>

						</div>

						<div class="blog-content">
							<h2 class="h3 mb-3"><a href="#">{{ Str::limit($post->title, 30) }}</a></h2>
							<p class="content">{{ Str::limit($post->summary, 180) }}</p>
						</div>

					</div>
				</div>
			@endforeach
			
		</div>

		<div class="view-collection text-center mt-4 mt-md-5">
			<a href="#" class="btn btn-secondary btn-lg">مشاهده همه وبلاگ</a>
		</div>

	</div>
</section>