@foreach ($comments->where('parent_id', null) as $comment)
<x-modal id="showCommentAnswerModal-{{ $comment->id }}" size="md">
	<x-slot name="title">ویرایش نظر کد - {{ $comment->id }}</x-slot>
	<x-slot name="body">
		<form action="{{ route('admin.post-comments.answer', $comment) }}" method="POST">
      @csrf
      <div class="row">

        <div class="col-12">
          <div class="form-group">
            <label for="body"><strong>نظر: </strong>{{ $comment->body }}</label>
            <textarea name="body" class="form-control" id="body" rows="5"></textarea>
          </div>
        </div>

      </div>

      <div class="modal-footer justify-content-center">
        <button class="btn btn-success" type="submit">ثبت پاسخ</button>
        <button class="btn btn-outline-danger" data-dismiss="modal">بستن</button>
      </div>

    </form>
	</x-slot>
</x-modal>
@endforeach
  