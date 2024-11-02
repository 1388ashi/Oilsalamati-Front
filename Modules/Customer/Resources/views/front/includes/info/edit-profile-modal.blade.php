<div class="modal fade" id="EditProfileModal" tabindex="-1" aria-labelledby="EditProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title" id="EditProfileModalLabel">ویرایش پروفایل</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="EditProfileForm" method="PATCH" action="{{ route('customer.profile.update') }}">
          <div class="form-row row-cols-lg-1 row-cols-md-1 row-cols-sm-1 row-cols-1">
            <div class="form-group">
              <label>نام:<span class="required">*</span></label>
              <input class="first-name" value="{{ $customer->first_name }}" type="text"/>
            </div>
            <div class="form-group">
              <label>نام خانوادگی:<span class="required">*</span></label>
              <input class="last-name" value="{{ $customer->last_name }}" type="text"/>
            </div>
            <div class="form-group">
              <label>موبایل:<span class="required">*</span></label>
              <input class="mobile" value="{{ $customer->mobile }}" type="text"/>
            </div>
            <div class="form-group">
              <label>شماره کارت:</label>
              <input class="card-number" value="{{ $customer->card_number }}" type="text"/>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="submit" class="btn btn-secondary m-0" onclick="editProfile(event)">ویرایش</button>
      </div>
    </div>
  </div>
</div>
