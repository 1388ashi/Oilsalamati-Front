@foreach($positions as $position)
<x-modal id="edit-position-{{ $position->id }}" size="lg">
  <x-slot name="title">ویرایش جایگاه</x-slot>
  <x-slot name="body">
    <form action="{{route('admin.positions.update', $position->id)}}" method="POST">
        @csrf
        @method('PATCH')
      <div class="modal-body">
        <div class="row">
          <div class="col-6">
            <div class="form-group">
              <label class="control-label">لیبل:<span class="text-danger">&starf;</span></label>
              <input type="text" class="form-control" name="label"  placeholder="لیبل را اینجا وارد کنید" value="{{ old('label', $position->label) }}" required autofocus>
            </div>
          </div>
          <div class="col-6">
            <div class="form-group">
                <label class="control-label">کلید:<span class="text-danger">&starf;</span></label>
                <input type="text" class="form-control" name="key"  placeholder="کلید را اینجا وارد کنید" value="{{ old('key', $position->key) }}" required autofocus>
            </div>
          </div>
        </div>
        <div class="">
          <div class="form-group">
            <label class="control-label">توضیحات:<span class="text-danger">&starf;</span></label>
            <textarea name="description" class="form-control" cols="120" rows="3">{{old('description',$position->description)}}</textarea>
          </div>
        </div>
        <div class="row">
          <div class="col-6">
            <div class="form-group">
              <label class="control-label">ارتفاع:<span class="text-danger">&starf;</span></label>
              <input type="number" class="form-control" name="height"  placeholder="ارتفاع را اینجا وارد کنید" value="{{ old('height', $position->height) }}" required autofocus>
            </div>
          </div>
          <div class="col-6">
            <div class="form-group">
              <label class="control-label">عرض:<span class="text-danger">&starf;</span></label>
              <input type="number" class="form-control" name="width"  placeholder="عرض را اینجا وارد کنید" value="{{ old('width', $position->width) }}" required autofocus>
            </div>
          </div>
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
                {{ old('status', $position->status) == 1 ? 'checked' : null }}
              />
              <span class="custom-control-label">فعال</span>
            </label>
          </div>
      </div>
      <div class="modal-footer justify-content-center">
          <button  class="btn btn-warning text-right item-right">به روزرسانی</button>
          <button class="btn btn-outline-danger  text-right item-right" data-dismiss="modal">برگشت</button>
      </div>
    </form>
  </x-slot>
</x-modal>
@endforeach
