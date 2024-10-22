@foreach ($questions as $question)
<x-modal id="showQuestionAnswerModal-{{ $question->id }}" size="md">
	<x-slot name="title">پاسخ نظر - {{ $question->id }}</x-slot>
	<x-slot name="body">
		<form action="{{ route('admin.product-questions.answer', $question) }}" method="POST">
      @csrf
      <div class="row">

        <input type="hidden" name="product_id" value="{{ $question->product_id }}">
        <input type="hidden" name="parent_id" value="{{ $question->id }}">

        <div class="col-12">
          <div class="form-group">
            <label for="body"><strong>نظر: </strong>{{ $question->body }}</label>
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
  