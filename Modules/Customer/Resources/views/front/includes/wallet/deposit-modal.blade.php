<div class="modal fade" id="DepositWalletModal" tabindex="-1" aria-labelledby="DepositWalletModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title" id="DepositWalletModalLabel">شارژ کیف پول</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="DepositWalletForm" class="add-address-from" method="POST" action="{{ route('customer.profile.deposit') }}">
          @csrf
          <div class="form-row row-cols-lg-1 row-cols-md-1 row-cols-sm-1 row-cols-1">
            <div class="form-group">
              <label>مبلغ بر حسب تومان:<span class="required">*</span></label>
              <input class="amount comma" name="amount" type="text"/>
            </div>
            <div class="form-group">
              <label>انتخاب درگاه<span class="required">*</span></label>
              <div class="row mt-3" id="GatwaySection">
                @foreach ($drivers as $driver)
                  <div class="col-6 col-xl-4 text-center mb-3">
                    <label for="formcheckoutRadio-{{ $driver['name'] }}" class="mb-2">
                      <img
                        class="blur-up lazyloaded p-2 gatway-img"
                        onclick="selectGatway(event)"
                        src="{{ asset($driver['image']) }}"
                        style="max-width: 104px; min-width: 104px; min-height: 84px; max-height: 84px; cursor: pointer;"
                      />
                    </label>
                  </div>
                @endforeach
              </div>

              {{-- <select class="payment-driver">
                <option value="">درگاه مورد نظر را انتخاب کنید</option>
                @foreach ($drivers as $driver)
                  <option value="{{ $driver['name'] }}">{{ $driver['label'] }}</option>
                @endforeach
              </select> --}}
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="submit" class="btn btn-primary m-0" onclick="depositWallet(event)">افزایش</button>
      </div>
    </div>
  </div>
</div>
