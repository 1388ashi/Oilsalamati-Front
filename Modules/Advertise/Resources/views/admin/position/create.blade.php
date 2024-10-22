<x-modal id="createPositionModal" size="lg">
  <x-slot name="title">ثبت جایگاه</x-slot>
  <x-slot name="body">
    <form action="{{route('admin.positions.store')}}" method="post">
      @csrf
      <div class="modal-body">
          <div class="row">
            <div class="col-6">
              <div class="form-group">
                <label class="control-label">لیبل:<span class="text-danger">&starf;</span></label>
                <input type="text" class="form-control" name="label"  placeholder="لیبل واحد را اینجا وارد کنید" value="{{ old('label') }}" required autofocus>
              </div>
            </div>
            <div class="col-6">
              <div class="form-group">
                  <label class="control-label">کلید:<span class="text-danger">&starf;</span></label>
                  <input type="text" class="form-control" name="key"  placeholder="کلید واحد را اینجا وارد کنید" value="{{ old('key') }}" required autofocus>
              </div>
            </div>
          </div>
            <div class="">
              <div class="form-group">
                <label class="control-label">توضیحات:<span class="text-danger">&starf;</span></label>
                <textarea name="description" class="form-control" cols="120" rows="3">{{old('description')}}</textarea>
              </div>
            </div>
            <div class="row">
              <div class="col-6">
                <div class="form-group">
                  <label class="control-label">ارتفاع:<span class="text-danger">&starf;</span></label>
                  <input type="number" class="form-control" name="height"  placeholder="ارتفاع را اینجا وارد کنید" value="{{ old('height') }}" required autofocus>
                </div>
              </div>
              <div class="col-6">
                <div class="form-group">
                  <label class="control-label">عرض:<span class="text-danger">&starf;</span></label>
                  <input type="number" class="form-control" name="width"  placeholder="عرض را اینجا وارد کنید" value="{{ old('width') }}" required autofocus>
                </div>
              </div>
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