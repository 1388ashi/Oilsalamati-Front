@foreach ($comments as $comment)
<x-modal id="editCommentModal-{{ $comment->id }}" size="md">
	<x-slot name="title">ویرایش نظر کد - {{ $comment->id }}</x-slot>
	<x-slot name="body">
		<form action="{{ route('admin.post-comments.update', $comment) }}" method="POST">
			@csrf
			@method('PUT')
			<div class="row">

				<div class="col-12">
					<div class="form-group">
						<label for="name" class="control-label">نام :</label>
						<input type="text" id="name" class="form-control" name="name" required value="{{ old('name', $comment->name) }}">
					</div>
				</div>

				<div class="col-12">
					<div class="form-group">
						<label for="email" class="control-label">ایمیل:</label>
						<input type="email" id="email" class="form-control" name="email" required value="{{ old('email', $comment->email) }}">
					</div>
				</div>

				<div class="col-12">
					<div class="form-group">
						<label for="body" class="control-label">نظر :<span class="text-danger">&starf;</span></label>
						<textarea name="body" class="form-control" id="body" rows="2">{{ $comment->body }}</textarea>
					</div>
				</div>

				<div class="col-12">
					<div class="form-group">
						<label for="label" class="control-label"> وضعیت: <span class="text-danger">&starf;</span></label>
							<select name="status" id="status" class="form-control">
								@foreach (config('comment.statuses') as $name => $label)
									<option 
										value="{{ $name }}"
										{{ $comment->status == $name ? 'selected' : null }}>
										{{ $label }}
									</option>	
								@endforeach
							</select>
						</label>
					</div>
				</div>

			</div>

			<div class="modal-footer justify-content-center mt-2">
				<button class="btn btn-warning" type="submit">بروزرسانی</button>
				<button class="btn btn-outline-danger" data-dismiss="modal">انصراف</button>
			</div>

		</form>
	</x-slot>
</x-modal>
@endforeach
