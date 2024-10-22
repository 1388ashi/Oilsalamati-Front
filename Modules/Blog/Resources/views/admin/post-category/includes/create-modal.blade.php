<div class="modal fade" id="createPostCategoryModal" style="display: none;" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content modal-content-demo">
      <div class="modal-header">
        <p class="modal-title" style="font-size: 20px;">ثبت دسته بندی مطلب جدید</p>
        <button aria-label="Close" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('admin.post-categories.store') }}" method="post" class="save">
          @csrf
          <div class="row">

            <div class="col-12">
              <div class="form-group">
                <label for="name" class="control-label">نام دسته بندی :<span class="text-danger">&starf;</span></label>
                <input type="text" id="name" class="form-control" name="name" required value="{{ old('name') }}">
              </div>
            </div>

            <div class="col-12">
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
            <button class="btn btn-primary" type="submit">ثبت و ذخیره</button>
            <button class="btn btn-outline-danger" data-dismiss="modal">انصراف</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
