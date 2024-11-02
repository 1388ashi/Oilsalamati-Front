<div class="modal fade" id="DepositWalletModal" tabindex="-1" aria-labelledby="DepositWalletModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title" id="DepositWalletModalLabel">برداشت از کیف پول</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="DepositWalletForm" class="add-address-from" method="POST" action="{{ route('customer.profile.deposit') }}">
          @csrf
          <div class="form-row row-cols-lg-1 row-cols-md-1 row-cols-sm-1 row-cols-1">
            <div class="form-group">
              <label>مبلغ بر حسب تومان:<span class="required">*</span></label>
              <input class="amount" name="amount" type="text"/>
            </div>
            <div class="form-group">
              <label>انتخاب درگاه<span class="required">*</span></label>
              <select class="payment-driver">
                <option value="">درگاه مورد نظر را انتخاب کنید</option>
                @foreach ($drivers as $driver)
                  <option value="{{ $driver['name'] }}">{{ $driver['label'] }}</option>
                @endforeach
              </select>
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
