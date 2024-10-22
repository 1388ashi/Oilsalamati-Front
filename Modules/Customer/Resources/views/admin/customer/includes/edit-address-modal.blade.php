@foreach($customer->addresses as $item)
<div class="modal fade mt-5" tabindex="-1" id="edit-address-{{ $item->id }}" role="dialog"
     aria-labelledby="modelTitleId" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form action="{{route('admin.addresses.update', $item->id)}}" method="POST">
        @csrf
        @method('PATCH')
        <div class="modal-header">
          <p class="modal-title font-weight-bolder">ویرایش آدرس</p>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-6">
              @php
                $city = $item->city;
                $provinceId = $city->province->id;
              @endphp
              <input type="hidden" name="customer_id" value="{{$customer->id}}">
              <div class="form-group">
                <label for="province_id">انتخاب استان : <span
                          class="text-danger">&starf;</span></label>
                <select name="province_id" class="form-control select2" required>
                  @foreach ($provinces as $province)
                    <option value="{{ $province->id }}"
                            @if ($province->id == $provinceId) selected @endif>{{ $province->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-6">
              <div class="form-group">
                <label for="city_id">انتخاب شهر : <span
                          class="text-danger">&starf;</span></label>
                <select name="city" class="form-control select2" required>
                  @foreach ($cities as $cityItem)
                    <option value="{{ $cityItem->id }}"
                            @if ($cityItem->id == $city->id) selected @endif>{{$cityItem->name}}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-6">
              <div class="form-group">
                <label>نام:<span class="text-danger">&starf;</span></label>
                <input type="text" class="form-control" name="first_name" placeholder="نام کاربر را وارد کنید" value="{{ old('first_name', $item->first_name) }}" required autofocus>
              </div>
            </div>
            <div class="col-6">
              <div class="form-group">
                <label>نام و نام خانوادگی :<span class="text-danger">&starf;</span></label>
                <input type="text" class="form-control" name="last_name" placeholder="نام کاربر را وارد کنید" value="{{ old('last_name', $item->last_name) }}" required autofocus>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-6">
              <div class="form-group">
                <label>کد پستی :<span class="text-danger">&starf;</span></label>
                <input type="text" class="form-control" name="postal_code" placeholder="کد پستی را وارد کنید"  value="{{ old('postal_code',$item->postal_code) }}" required autofocus>
              </div>
            </div>
            <div class="col-6">
              <div class="form-group">
                <label>موبایل :<span class="text-danger">&starf;</span></label>
                <input type="text" class="form-control" name="mobile" placeholder="موبایل را وارد کنید" value="{{ old('mobile',$item->mobile) }}" required autofocus>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label>آدرس :<span class="text-danger">&starf;</span></label>
                <textarea name="address" class="form-control" cols="70" rows="3">{{old('address',$item->address)}}</textarea>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer justify-content-center">
          <button class="btn btn-warning text-right item-right">به روزرسانی</button>
          <button class="btn btn-outline-danger  text-right item-right" data-dismiss="modal">برگشت</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach