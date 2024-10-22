<x-modal id="createSliderModal" size="md">
  <x-slot name="title">ثبت اسلایدر جدید</x-slot>
  <x-slot name="body">
        <form action="{{route('admin.sliders.store')}}" method="post" class="save" enctype="multipart/form-data">
          @csrf
          <div class="modal-body">
              <input type="hidden" name="group" value="{{$group}}">

              <div class="form-group">
                <label class="control-label">عنوان :<span class="text-danger">&starf;</span></label>
                <input type="text" class="form-control" name="title"  placeholder="عنوان را اینجا وارد کنید" value="{{ old('title') }}" required autofocus>
              </div>
              <div class="form-group">
                <label class="control-label">توضیحات:<span class="text-danger">&starf;</span></label>
                <textarea name="description" class="form-control"  cols="70" rows="3">{{old('description')}}</textarea>
              </div>
              <div class="form-group">
                <label for="image" class="control-label"> تصویر: <span class="text-danger">&starf;</span></label>
                <input type="file" id="image" class="form-control" name="image" value="{{ old('image') }}">
              </div>
              <div class="row">
                <div class="col-12 form-group">
                    <label class="control-label">نوع لینک :</label><span class="text-danger">&starf;</span>
                    <select id="linkableType" onchange="toggleInput()" name="linkable_type"
                        class="form-control">
                        <option value="self_link" class="custom-menu">لینک دلخواه</option>
                        @foreach ($linkables as $link)
                        <option value="{{ $link['unique_type'] }}" class="model">{{ $link['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 form-group" id="divLinkable" style="display: none">
                    <label class="control-label">آیتم های لینک :</label>
                    <select id="linkableId" name="linkable_id" class="form-control select2">
                        <option class="custom-menu">انتخاب</option>
                    </select>
                </div>
                <div class="col-12 form-group">
                    <label class="control-label">لینک دلخواه :</label>
                    <input type="text" id="link" name="link" class="form-control" disabled>
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
          </div>
          <div class="modal-footer justify-content-center">
              <button  class="btn btn-primary  text-right item-right">ثبت</button>
              <button class="btn btn-outline-danger  text-right item-right" data-dismiss="modal">برگشت</button>
          </div>
      </form>
  </x-slot>
</x-modal>