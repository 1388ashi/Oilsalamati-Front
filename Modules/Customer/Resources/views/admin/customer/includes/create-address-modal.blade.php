<div class="modal fade" id="createAddresseModal">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <form action="{{route('admin.addresses.store')}}" method="post">
          @csrf
          <div class="modal-header">
            <p class="modal-title font-weight-bolder">افزودن آدرس جدید</p>
            <button class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-6">
                <input type="hidden" name="customer_id" value="{{$customer->id}}">
                <div class="form-group">
                  <label for="province_id">انتخاب استان : <span class="text-danger">&starf;</span></label>
                  <select name="province_id" id="province_id" class="form-control select2" required>
                    <option value="">استان را انتخاب کنید</option>
                    @foreach ($provinces as $province)
                      <option value="{{ $province->id }}">{{ $province->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-6">
                <div class="form-group" id="city_id_container" style="display: none;">
                  <label for="city_id">انتخاب شهر : <span
                            class="text-danger">&starf;</span></label>
                  <select name="city" id="city_id" class="form-control select2" required>
                    <option value="">شهر را انتخاب کنید</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-6">
                <div class="form-group">
                  <label>نام : <span class="text-danger">&starf;</span></label>
                  <input type="text" class="form-control" name="first_name" placeholder="نام کاربر را وارد کنید"
                         value="{{ old('first_name') }}" required autofocus>
                </div>
              </div>
              <div class="col-6">
                <div class="form-group">
                  <label>نام و نام خانوادگی :<span class="text-danger">&starf;</span></label>
                  <input type="text" class="form-control" name="last_name" placeholder="نام خانوادگی کاربر را وارد کنید"
                         value="{{ old('last_name') }}" required autofocus>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-6">
                <div class="form-group">
                  <label>کد پستی :<span class="text-danger">&starf;</span></label>
                  <input type="text" class="form-control" name="postal_code" placeholder="کد پستی را وارد کنید"
                         value="{{ old('postal_code') }}" required autofocus>
                </div>
              </div>
              <div class="col-6">
                <div class="form-group">
                  <label>موبایل :<span class="text-danger">&starf;</span></label>
                  <input type="text" class="form-control" name="mobile" placeholder="موبایل را وارد کنید"
                         value="{{ old('mobile') }}" required autofocus>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <div class="form-group">
                  <label>آدرس :<span class="text-danger">&starf;</span></label>
                  <textarea name="address" class="form-control" cols="70" rows="3">{{old('address')}}</textarea>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer justify-content-center">
            <button class="btn btn-primary  text-right item-right">ثبت</button>
            <button class="btn btn-outline-danger  text-right item-right" data-dismiss="modal">برگشت</button>
          </div>
        </form>
      </div>
    </div>
  </div>