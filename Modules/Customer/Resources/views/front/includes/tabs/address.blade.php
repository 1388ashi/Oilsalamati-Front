<div class="tab-pane fade h-100" id="address">
  <div class="address-card mt-0 h-100">
    <div class="top-sec d-flex-justify-center justify-content-between mb-4">
      <h2 class="mb-0">آدرس ها</h2>
      <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addNewAddressModal">
        <i class="icon anm anm-plus-r ms-1"></i> آدرس جدید
      </button>
    </div>

    <div class="address-book-section">
      <div class="row g-4">
        @forelse ($customer->addresses as $address)
        <div class="row-cols-lg-3 row-cols-md-2 row-cols-sm-2 row-cols-1"  id="AddressSection">
          <div class="address-select-box active" id="AddressBox-{{ $address->id }}">
            <div class="address-box bg-block">
              <div class="top d-flex-justify-center justify-content-between mb-3">
                <h5 class="m-0 address-receiver" >{{ $address->first_name .' '. $address->last_name }}</h5>
              </div>
              <div class="middle">
                <div class="address mb-2 text-muted">
                  <address class="m-0 address-detail">{{ $address->address }}</address>
                </div>
                <div class="number">
                  <p>تلفن همراه: <span class="address-mobile text-muted" dir="ltr">{{ $address->mobile }}</span></p>
                </div>
                <div>
                  <p>کد پستی: <span class="address-postal-code text-muted">{{ $address->postal_code }}</span></p>
                </div>
              </div>
              <div class="bottom d-flex-justify-center justify-content-between">
                <button type="button" class="bottom-btn btn btn-gray btn-sm edit-btn" data-bs-toggle="modal" data-bs-target="#EditAddressModal-{{ $address->id }}">ویرایش</button>
                <button type="button" class="bottom-btn btn btn-gray btn-sm delete-btn" data-address-id="{{ $address->id }}" onclick="deleteAddress(event)">حذف</button>
              </div>
            </div>
          </div>
        </div>
        @empty
        <div class="bg-danger p-3 text-center rounded">
          <div class="col-12">
            <p class="text-light fs-5">آدرسی برای شما ثبت نشده</p>
          </div>
        </div>
        @endforelse
      </div>
    </div>
  </div>

  @include('customer::front.includes.address.create-modal')
  @include('customer::front.includes.address.edit-modal')

</div>