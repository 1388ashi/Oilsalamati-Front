@foreach ($comments as $comment)
	<div class="modal fade" id="show-comment-detail-modal-{{ $comment->id }}" style="display: none;" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content modal-content-demo">
				<div class="modal-header">
					<p class="modal-title" style="font-size: 20px;">مشاهده نظر کد - {{ $comment->id }}</p>
					<button aria-label="Close" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-12 my-1">
							<strong class="fs-17">کاربر :</strong>
							<span class="fs-16">{{ $comment->creator->full_name ?? $comment->creator->mobile }}</span>
						</div>
						<div class="col-12 my-1">
							<strong class="fs-17">محصول :</strong>
							<span class="fs-16">{{ $comment->product->title }}</span>
						</div>
						<div class="col-12 my-1">
							<strong class="fs-17">امتیاز :</strong>
							<span class="fs-16">
								@if ($comment->rate > 0)
								  @for ($i = 1; $i <= $comment->rate; $i++)
									<i class="fa fa-star text-warning"></i>
								  @endfor
								@endif
								@php $remainingRate = 5 - $comment->rate @endphp
								@if ($remainingRate > 0)
								  @for ($i = 1; $i <= $remainingRate; $i++)
									<i class="fa fa-star-o text-warning"></i>
								  @endfor
								@endif
							  </span>
						</div>
						<div class="col-12 my-1">
							<strong class="fs-17">نظر :</strong>
							<span class="fs-16">{{ $comment->body }}</span>
						</div>
					</div>
					<form
					 	action="{{ route('admin.product-comments.assign-status') }}"
						method="POST"
						id="assign-status-form-{{ $comment->id }}">
						@csrf
						<input type="hidden" value="{{ $comment->id }}" name="id">
						<input type="hidden" value="" name="status" id="status-{{ $comment->id }}">
					</form>
				</div>
				<div class="modal-footer justify-content-center mt-2">
					<button class="btn btn-success mx-1" onclick="assignStatus('approved', '{{ $comment->id }}')">تایید نظر</button>
					<button class="btn btn-danger mx-1" onclick="assignStatus('reject', '{{ $comment->id }}')">رد نظر</button>
					<button class="btn btn-warning mx-1" onclick="assignStatus('pending', '{{ $comment->id }}')">در انتظار بررسی</button>
				</div>
			</div>
		</div>
	</div>
@endforeach
