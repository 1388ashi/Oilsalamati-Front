<div class="address-select-box d-none" id="ExampleAddressBox">
  <div class="address-box bg-block radius-16">
    <div class="top d-flex-justify-center justify-content-between mb-3">
      <h5 class="m-0 address-receiver"></h5>
      <div class="d-flex gap-1 align-items-center">
        <button type="button" class="btn btn-primary btn-sm edit-btn address-operation-button" data-bs-toggle="modal" data-bs-target="">
          <i class="fe fe-edit"></i>
        </button>
        <button type="button" class="btn btn-secondary btn-sm delete-btn address-operation-button" data-address-id="" onclick="deleteAddress(event)">
          <i class="fe fe-trash"></i>
        </button>
      </div>
    </div>
    <div class="middle">
      <div class="address mb-2 text-muted">
        <address class="m-0 address-detail"></address>
      </div>
      <div class="number">
        <p>تلفن همراه: <a class="address-mobile text-muted" dir="ltr"></a></p>
      </div>
      <div>
        <p>کد پستی: <span class="address-postal-code text-muted"></span></p>
      </div>
    </div>
  </div>
</div>