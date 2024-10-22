<x-modal id="createFaqModal" size="md">
  <x-slot name="title">ثبت سوال متداول</x-slot>
  <x-slot name="body">
    <form action="{{route('admin.faqs.store')}}" method="post">
          @csrf
      <div class="modal-body">
          <div class="form-group">
            <label class="control-label">سوال :<span class="text-danger">&starf;</span></label>
            <textarea name="question" class="form-control" cols="70" rows="3">{{old('question')}}</textarea>
          </div>
          <div class="form-group">
            <label class="control-label">پاسخ:<span class="text-danger">&starf;</span></label>
            <textarea name="answer" class="form-control"  cols="70" rows="3">{{old('answer')}}</textarea>
          </div>
          <div class="row">
              <div class="col-md-4">
                  <div class="form-group">
                    <label for="label" class="control-label"> وضعیت: </label>
                    <label class="custom-control custom-checkbox">
                      <input
                        type="checkbox"
                        class="custom-control-input"
                        name="status"
                        value="1"
                        {{ old('status', 1) == 1 ? 'checked' : null }}
                      />
                      <span class="custom-control-label">فعال</span>
                    </label>
              </div>
          </div>
      </div>
      <div class="modal-footer justify-content-center">
          <button  class="btn btn-primary  text-right item-right">ثبت</button>
          <button class="btn btn-outline-danger  text-right item-right" data-dismiss="modal">برگشت</button>
      </div>
      </form>
    </x-slot>
  </x-modal>