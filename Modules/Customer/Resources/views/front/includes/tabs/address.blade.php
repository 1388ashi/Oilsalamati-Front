<div class="tab-pane fade h-100" id="address">
  <div class="address-card mt-0 h-100">
    <div class="top-sec d-flex-justify-center justify-content-between align-items-center mb-4">
      <h2 class="m-0">آدرس ها</h2>
      <button type="button" class="btn btn-primary btn-sm radius-16" data-bs-toggle="modal" data-bs-target="#addNewAddressModal">
        <i class="icon anm anm-plus-r ms-1"></i> آدرس جدید
      </button>
    </div>

    <hr>

    <div class="address-book-section">
      @if ($customer->addresses->isEmpty())
        <div class="row p-3 text-center rounded" id="EmptyAddressSection">
          <div class="col-12">
            <p class="text-danger fs-5">
              <i class="icon anm anm-location ms-1"></i>
              <span>آدرسی برای شما ثبت نشده</span>  
            </p>
          </div>
        </div>
      @endif
      <div class="row g-4 row-cols-lg-3 row-cols-md-2 row-cols-sm-2 row-cols-1"  id="AddressSection">
        @foreach ($customer->addresses as $address)
          <div class="address-select-box active" id="AddressBox-{{ $address->id }}">
            <div class="address-box bg-block radius-16">
              <div class="top d-flex-justify-center justify-content-between mb-3">
                <h5 class="m-0 address-receiver" >{{ $address->first_name .' '. $address->last_name }}</h5>
                <div class="d-flex gap-1 align-items-center">
                  <button type="button" class="btn btn-primary btn-sm edit-btn address-operation-button" data-bs-toggle="modal" data-bs-target="#EditAddressModal-{{ $address->id }}">
                    <i class="fe fe-edit"></i>
                  </button>
                  <button type="button" class="btn btn-secondary btn-sm delete-btn address-operation-button" data-address-id="{{ $address->id }}" onclick="deleteAddress(event)">
                    <i class="fe fe-trash"></i>
                  </button>
                </div>
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
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  @include('customer::front.includes.address.create-modal')
  @include('customer::front.includes.address.edit-modal')

</div>