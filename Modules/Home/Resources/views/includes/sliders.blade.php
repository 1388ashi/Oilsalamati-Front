<section class="slideshow slideshow-wrapper">
	<div class="container" style="margin-top: 10px;">
		<div class="row">

			<div class="col-12 col-sm-12 col-md-12 col-lg-8">
				<div class="home-slideshow slideshow-medium slick-arrow-dots circle-arrow" dir="ltr">
					 @foreach ($response['sliders'] as $slider) 
						 <div class="slide">
							<div class="slideshow-wrap bg-size rounded-4">
								<img
									class="bg-img rounded-4 blur-up lazyload"
									data-src="{{ asset($slider->image->url) }}"
									src="{{ asset($slider->image->url) }}"
									alt="{{ $slider->title }}" 
									title="{{ $slider->title }}"
									width="1148"
									height="710"
								/>
							</div>
						</div> 
					@endforeach
				</div>
			</div>

			{{-- <div class="col-12 col-sm-12 col-md-12 col-lg-4 mt-4 mt-lg-0">
				<div class="collection-banner-grid">
					<div class="row sp-row">

						@php
							$advertise = $response['advertise']['bottom_left'];
						@endphp

						<div class="col-12 col-sm-12 col-md-6 col-lg-12 collection-banner-item">
							<div class="collection-item sp-col">
								<a href="{{ $advertise->link }}" @if($advertise->new_tab) target="_blank" @endif class="rounded-4 zoom-scal clr-none" >
									<div class="img">
										<img
											class="rounded-4 w-100 blur-up lazyload"
											alt="{{ $advertise->title }}"
											data-src="{{ $advertise->picture }}"
											src="{{ $advertise->picture }}"
											width="454"
											height="268"
										/>
									</div>
								</a>
							</div>
						</div>

						@php
							$advertise = $response['advertise']['bottom_right'];
						@endphp

						<div class="col-12 col-sm-12 col-md-6 col-lg-12 collection-banner-item">
							<div class="collection-item sp-col">
								<a href="{{ $advertise->link }}" @if($advertise->new_tab) target="_blank" @endif class="rounded-4 zoom-scal clr-none" >
									<div class="img">
										<img
											class="rounded-4 w-100 blur-up lazyload"
											src="{{ $advertise->picture }}"
											alt="{{ $advertise->title }}"
											width="454"
											height="268"
										/>
									</div>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div> --}}

		</div>
	</div>
</section>