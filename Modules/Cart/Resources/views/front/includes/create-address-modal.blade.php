<div class="modal fade" id="addNewAddressModal" tabindex="-1" aria-labelledby="addNewAddressLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title" id="addNewAddressLabel">جزئیات آدرس</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form class="add-address-from" action="{{ route('customer.addresses.store') }}">
          <div class="form-row row-cols-lg-1 row-cols-md-2 row-cols-sm-1 row-cols-1">
            <div class="form-group">
              <label for="province" class="d-none" >استان<span class="required">*</span></label>
              <select id="province" class="province" onchange="appendCities(event)"></select>
            </div>
            <div class="form-group">
              <label for="city" class="d-none">شهر<span class="required">*</span></label>
              <select name="city" id="city" class="city">
                <option value="">ابتدا استان را انتخاب کنید</option>
              </select>
            </div>
            <div class="form-group">
              <label for="first_name" class="d-none">نام</label>
              <input class="first_name" name="first_name" placeholder="نام" id="first_name" type="text"/>
            </div>
            <div class="form-group">
              <label for="last_name" class="d-none">نام خانوادگی</label>
              <input class="last_name" name="last_name" placeholder="نام خانوادگی" id="last_name" type="text" />
            </div>
            <div class="form-group">
              <label for="postal_code" class="d-none">کد پستی</label>
              <input class="postal_code" name="postal_code" placeholder="کد پستی" id="postal_code" type="text"/>
            </div>
            <div class="form-group">
              <label for="mobile" class="d-none">موبایل<span class="required">*</span></label>
              <input class="mobile" name="mobile" placeholder="موبایل" id="mobile" type="text"/>
            </div>
            <div class="form-group">
              <label for="address" class="d-none">آدرس<span class="required">*</span></label>
              <input class="address" name="address" placeholder="آدرس خیابان" id="address" type="text"/>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-primary m-0" onclick="submitAddress(event, 'POST')"><span>افزودن آدرس</span></button>
      </div>
    </div>
  </div>  
</div>