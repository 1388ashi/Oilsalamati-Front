@foreach ($customer->addresses as $address)
<div class="modal fade" id="EditAddressModal-{{ $address->id }}" tabindex="-1" aria-labelledby="EditAddressModal-{{ $address->id }}Label" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
  <div class="modal-content">
    <div class="modal-header">
      <h2 class="modal-title" id="EditAddressModal-{{ $address->id }}Label">ویرایش جزئیات آدرس</h2>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
      <form id="UpdateAddressForm-{{ $address->id }}" class="edit-address-from" method="POST" action="{{ route('customer.addresses.update', $address) }}">
        <div class="form-row row-cols-lg-2 row-cols-md-2 row-cols-sm-1 row-cols-1">

          <div class="form-group">
            <label class="d-none">استان<span class="required">*</span></label>
            <select class="province" onchange="appendCities(event)">
              @foreach ($provinces->sortBy('name') as $province)
                <option value="{{ $province->id }}" @if($province->id == $address->city->province_id) selected @endif>{{ $province->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label class="d-none">شهر<span class="required">*</span></label>
            <select class="city">
              @php
                $province = $provinces->where('id', $address->city->province_id)->first();  
              @endphp
              @foreach ($province->cities->sortBy('name') as $city)
                <option value="{{ $city->id }}" @if($city->id == $address->city_id) selected @endif>{{ $city->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label class="d-none">نام</label>
            <input class="first-name" value="{{ $address->first_name }}" placeholder="نام" type="text"/>
          </div>

          <div class="form-group">
            <label class="d-none">نام خانوادگی</label>
            <input class="last-name" value="{{ $address->last_name }}" placeholder="نام خانوادگی" type="text" />
          </div>

          <div class="form-group">
            <label class="d-none">کد پستی</label>
            <input class="postal-code" value="{{ $address->postal_code }}" placeholder="کد پستی" type="text"/>
          </div>

          <div class="form-group">
            <label class="d-none">موبایل<span class="required">*</span></label>
            <input class="mobile" value="{{ $address->mobile }}" placeholder="موبایل" type="text"/>
          </div>

          <div class="form-group">
            <label class="d-none">آدرس<span class="required">*</span></label>
            <input class="address" value="{{ $address->address }}" placeholder="آدرس" type="text"/>
          </div>

        </div>
      </form>
    </div>
    <div class="modal-footer justify-content-center">
      <button type="submit" class="btn btn-primary m-0" onclick="submitAddress(event, 'PUT')">
        <span>ذخیره آدرس</span>
      </button>
    </div>
  </div>
</div>
</div>
@endforeach