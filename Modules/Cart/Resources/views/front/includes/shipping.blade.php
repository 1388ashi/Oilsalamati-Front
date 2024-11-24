<div class="tab-pane" id="steps2">
  <div class="banks-card mt-0 h-100">
    <div class="top-sec d-flex-justify-center justify-content-between mb-4">
      <h2 class="mb-0">مکان تحویل سفارش</h2>
      <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addNewAddressModal">
        <i class="icon anm anm-plus-r ms-1"></i>افزودن آدرس جدید
      </button>
    </div>

    <div class="bank-book-section">
      <div id="AddressSection" class="row g-4 row-cols-lg-3 row-cols-md-2 row-cols-sm-2 row-cols-1">
        <div class="address-select-box d-none" id="AddressBox">
          <div class="address-box bg-block">
            <div class="middle">
              <div class="card-number mb-3">
                <div class="customRadio clearfix">
                  <input id="" value="" name="address_id" type="radio" class="radio"/>
                  <label class="address-detail" for="" class="mb-2"></label>
                  <p class="text-muted col-12 address-receiver mb-0"></p>
                  <p class="text-muted col-12 address-postal-code"></p>
                </div>
              </div>
            </div>
            <div class="bottom d-flex-justify-left">

              <button type="button" class="btn btn-primary btn-sm edit-btn address-operation-button" data-target-modal-id="" onclick="openEditAddressModal(event)">
                <i class="fe fe-edit"></i>
              </button>

              <button 
                type="button" 
                class="btn btn-secondary btn-sm delete-btn address-operation-button" 
                data-delete-address-url=""
                data-address-id=""
                onclick="confrimDeletingAddress(event)">
                <i class="fe fe-trash"></i>
              </button>

            </div>
          </div>
        </div>
        @foreach ($user->addresses as $address)
          <div class="address-select-box" id="AddressBox-{{ $address->id }}">
            <div class="address-box bg-block">
              <div class="middle d-flex justify-content-between">

                

                <input 
                  id="formcheckoutRadio-{{ $address->id }}" 
                  data-url="{{ route('customer.shippings.getShippableForAddress', ['address' => $address->id]) }}" 
                  value="{{ $address->id }}" 
                  name="address_id" 
                  type="radio" 
                  class="radio"
                />

                <div>
                  <button 
                    type="button"
                    class="btn btn-primary btn-sm edit-btn address-operation-button" 
                    data-target-modal-id="editAddressModal-{{ $address->id }}" 
                    onclick="openEditAddressModal(event)">
                    <i class="fe fe-edit"></i>
                  </button>
    
                  <button 
                    type="button" 
                    class="btn btn-secondary btn-sm delete-btn address-operation-button" 
                    data-delete-address-url="{{ route('customer.addresses.destroy', $address) }}"
                    data-address-id="{{ $address->id }}"
                    onclick="confrimDeletingAddress(event)">
                    <i class="fe fe-trash"></i>
                  </button>
                </div>

                {{-- <div class="card-number mb-3">
                  <div class="customRadio clearfix">
                    <input 
                      id="formcheckoutRadio-{{ $address->id }}" 
                      data-url="{{ route('customer.shippings.getShippableForAddress', ['address' => $address->id]) }}" 
                      value="{{ $address->id }}" 
                      name="address_id" 
                      type="radio" 
                      class="radio"
                    />
                    <label class="address-detail" for="formcheckoutRadio-{{ $address->id }}" class="mb-2">
                      {{ $address->city->province->name }} - {{ $address->city->name }} - {{ $address->address }}
                    </label>
                    <p class="col-12 address-receiver mb-1 d-flex align-items-center gap-2">
                      <i class="fe fe-map text-dark"></i>
                      <span class="text-muted">{{ $address->city->province->name }} - {{ $address->city->name }} - {{ $address->address }}</span>
                    </p>
                    <p class="col-12 address-receiver mb-1 d-flex align-items-center gap-2">
                      <i class="fe fe-user text-dark"></i>
                      <span class="text-muted">{{ $address->first_name .' '. $address->last_name }} - {{ $address->mobile }}</span>
                    </p>
                    <p class="text-muted col-12 address-postal-code">کد پستی : {{ $address->postal_code }}</p>
                  </div>
                </div> --}}

              </div>

              <div class="d-flex flex-column">
                <p class="col-12 address mb-1 d-flex align-items-center gap-2">
                  <i class="fe fe-map"></i>
                  <span class="text-muted">{{ $address->city->province->name }} - {{ $address->city->name }} - {{ $address->address }}</span>
                </p>
                <p class="col-12 address-receiver mb-1 d-flex align-items-center gap-2">
                  <i class="fe fe-user"></i>
                  <span class="text-muted">{{ $address->first_name .' '. $address->last_name }} - {{ $address->mobile }}</span>
                </p>
                <p class="col-12 address-postal-code d-flex align-items-center gap-2">
                  <i class="fe fe-link"></i>
                  <span class="text-muted">کد پستی : {{ $address->postal_code }}</span>
                </p>
              </div>

              {{-- <div class="bottom d-flex-justify-left">

                <button 
                  type="button"
                  class="btn btn-primary btn-sm edit-btn address-operation-button" 
                  data-target-modal-id="editAddressModal-{{ $address->id }}" 
                  onclick="openEditAddressModal(event)">
                  <i class="fe fe-edit"></i>
                </button>
  
                <button 
                  type="button" 
                  class="btn btn-secondary btn-sm delete-btn address-operation-button" 
                  data-delete-address-url="{{ route('customer.addresses.destroy', $address) }}"
                  data-address-id="{{ $address->id }}"
                  onclick="confrimDeletingAddress(event)">
                  <i class="fe fe-trash"></i>
                </button>

              </div> --}}

            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
  <!--مکان تحویل-->

  <!-- شیوه ارسال-->
  <div class="banks-card mt-3 h-100">

    <div class="top-sec d-flex-justify-center justify-content-between mb-4">
      <h2 class="mb-0">شیوه ارسال</h2>
    </div>

    <div class="address-select-box d-none" id="ShippingBox">
      <div class="address-box bg-block h-100">
        <div class="top bank-logo d-flex-justify-center justify-content-between mb-3">
          <img src="" class="bank-logo" width="40"/>
        </div>
        <div class="middle">
          <div class="card-number mb-3">
            <div class="customRadio clearfix">
              <input id="" value="" name="shipping_id" type="radio" class="radio"/>
              <label for="" class="mb-2"></label>
            </div>
          </div>
          <div class="name-validity d-flex-justify-center justify-content-between">
            <div class="left">
              <h6>هزینه ارسال</h6>
            </div>
            <div class="right">
              <h6 class="shipping-price"></h6>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="bank-book-section"> 
      <div class="row g-4 row-cols-lg-3 row-cols-md-2 row-cols-sm-2 row-cols-1" id="ShippingSection">

      </div>
    </div>
  </div>

  <div class="d-flex justify-content-between my-4">
    <button type="button" class="btn btn-secondary ms-1" id="steps2-btnPrevious">مرحله قبل</button>
    <button type="button" class="btn btn-primary me-1" id="steps2-btnNext">مرحله بعد</button>
  </div>

  @include('cart::front.includes.create-address-modal')
  @include('cart::front.includes.edit-address-modal')

</div>