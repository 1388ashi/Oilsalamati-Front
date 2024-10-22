@foreach($faqs as $faq)
<x-modal id="edit-faq-{{ $faq->id }}" size="md">
  <x-slot name="title">ویرایش سوال</x-slot>
  <x-slot name="body">
      <form action="{{route('admin.faqs.update', [$faq->id])}}" method="POST">
          @csrf
          @method('PATCH')
      <div class="modal-body">
        <div class="form-group">
          <label class="control-label">سوال :<span class="text-danger">&starf;</span></label>
          <textarea name="question" class="form-control" cols="70">{{old('question',$faq->question)}}</textarea>
        </div>
        <div class="form-group">
          <label class="control-label">پاسخ:<span class="text-danger">&starf;</span></label>
          <textarea name="answer" class="form-control" cols="70">{{old('answer',$faq->answer)}}</textarea>
        </div>
          <div class="form-group">
            <label for="label" class="control-label"> وضعیت: </label>
            <label class="custom-control custom-checkbox">
              <input
                type="checkbox"
                class="custom-control-input"
                name="status"
                id="status"
                value="1"
                {{ old('status', $faq->status) == 1 ? 'checked' : null }}
              />
              <span class="custom-control-label">فعال</span>
            </label>
          </div>
      </div>
      <div class="modal-footer justify-content-center">
          <button type="submit" class="btn btn-warning text-right item-right">به روزرسانی</button>
          <button type="button" class="btn btn-outline-danger text-right item-right" data-dismiss="modal">برگشت</button>
      </div>
    </form>
  </x-slot>
</x-modal>
@endforeach
