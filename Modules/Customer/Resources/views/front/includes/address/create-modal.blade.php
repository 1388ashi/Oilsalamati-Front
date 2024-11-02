<div class="modal fade" id="addNewAddressModal" tabindex="-1" aria-labelledby="addNewAddressModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title" id="addNewModalLabel">جزئیات آدرس</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="" class="add-address-from" method="POST" action="{{ route('customer.addresses.store') }}">
          <div class="form-row row-cols-lg-2 row-cols-md-2 row-cols-sm-1 row-cols-1">

            <div class="form-group">
              <label class="d-none">استان<span class="required">*</span></label>
              <select class="province" onchange="appendCities(event)">
                @foreach ($provinces->sortBy('name') as $province)
                  <option value="{{ $province->id }}">{{ $province->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group">
              <label class="d-none">شهر<span class="required">*</span></label>
              <select class="city">
                <option value="">ابتدا استان را انتخاب کنید</option>
              </select>
            </div>

            <div class="form-group">
              <label class="d-none">نام</label>
              <input class="first-name" placeholder="نام" type="text"/>
            </div>

            <div class="form-group">
              <label class="d-none">نام خانوادگی</label>
              <input class="last-name" placeholder="نام خانوادگی" type="text" />
            </div>

            <div class="form-group">
              <label class="d-none">کد پستی</label>
              <input class="postal-code" placeholder="کد پستی" type="text"/>
            </div>

            <div class="form-group">
              <label class="d-none">موبایل<span class="required">*</span></label>
              <input class="mobile" placeholder="موبایل" type="text"/>
            </div>

            <div class="form-group">
              <label class="d-none">آدرس<span class="required">*</span></label>
              <input class="address" placeholder="آدرس" type="text"/>
            </div>

          </div>
        </form>
      </div>
      <div class="modal-footer justify-content-center" onclick="submitAddress(event, 'POST')">
        <button type="submit" class="btn btn-primary m-0" class="submit-btn">
          <span>افزودن آدرس</span>
        </button>
      </div>
    </div>
  </div>
</div>