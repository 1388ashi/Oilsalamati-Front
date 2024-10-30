@foreach ($user->addresses as $address)
  <div class="modal fade" id="editAddressModal-{{ $address->id }}" tabindex="-1" aria-labelledby="editAddressModal{{ $address->id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h2 class="modal-title" id="editAddressModal{{ $address->id }}Label">جزئیات آدرس</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form class="add-address-from" id="UpdateAddressForm-{{ $address->id }}" action="{{ route('customer.addresses.update', $address) }}">
            <div class="form-row row-cols-lg-1 row-cols-md-2 row-cols-sm-1 row-cols-1">
              <div class="form-group">
                <label for="province" class="d-none" >استان<span class="required">*</span></label>
                <select class="province" onchange="appendCities(event, @json($address->city_id))"></select>
              </div>
              <div class="form-group">
                <label for="city" class="d-none">شهر<span class="required">*</span></label>
                <select class="city"></select>
              </div>
              <div class="form-group">
                <label for="first_name" class="d-none">نام</label>
                <input class="first_name" placeholder="نام" type="text" value="{{ $address->first_name }}"/>
              </div>
              <div class="form-group">
                <label for="last_name" class="d-none">نام خانوادگی</label>
                <input class="last_name" placeholder="نام خانوادگی" type="text"  value="{{ $address->last_name }}"/>
              </div>
              <div class="form-group">
                <label for="postal_code" class="d-none">کد پستی</label>
                <input class="postal_code" placeholder="کد پستی" type="text" value="{{ $address->postal_code }}"/>
              </div>
              <div class="form-group">
                <label for="mobile" class="d-none">موبایل<span class="required">*</span></label>
                <input class="mobile" placeholder="موبایل" type="text" value="{{ $address->mobile }}"/>
              </div>
              <div class="form-group">
                <label for="address" class="d-none">آدرس<span class="required">*</span></label>
                <input class="address" placeholder="آدرس خیابان" type="text" value="{{ $address->address }}"/>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer justify-content-center">
          @php
            $formId = 'UpdateAddressForm-' . $address->id;
          @endphp
          <button type="button" class="btn btn-primary m-0" onclick="submitAddress(event, 'PUT')">
            <span>بروزرسانی آدرس</span>
          </button>
        </div>
      </div>
    </div>
  </div>
@endforeach