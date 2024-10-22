@foreach ($questions as $question)
<x-modal id="editQuestionModal-{{ $question->id }}" size="md">
	<x-slot name="title">ویرایش پرسش کد - {{ $question->id }}</x-slot>
	<x-slot name="body">
		<div class="row justify-content-center">
			<button class="btn btn-success mx-1" onclick="assignStatus('approved', '{{ $question->id }}')">تایید شده</button>
			<button class="btn btn-danger mx-1" onclick="assignStatus('rejected', '{{ $question->id }}')">رد شده</button>
			<button class="btn btn-warning mx-1" onclick="assignStatus('pending', '{{ $question->id }}')">در انتظار تایید</button>
		</div>

		<form 
			id="assign-status-form-{{ $question->id }}"
			action="{{ route('admin.product-questions.assign-status') }}" 
			method="POST">
			@csrf
			@method('PATCH')
			<input type="hidden" name="id" value="{{ $question->id }}">
			<input type="hidden" name="status" id="status-{{ $question->id }}" value="">
		</form>
	</x-slot>
</x-modal>
@endforeach
