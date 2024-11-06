<div class="modal fade" id="WithdrawWalletModal" tabindex="-1" aria-labelledby="WithdrawWalletModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title" id="WithdrawWalletModalLabel">برداشت از کیف پول</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="WithdrawWalletForm" class="add-address-from" method="POST" action="{{ route('customer.withdraws.store') }}">
          @csrf
          <div class="form-row row-cols-lg-1 row-cols-md-1 row-cols-sm-1 row-cols-1">
            <div class="form-group">
              <label>مبلغ بر حسب تومان:<span class="required">*</span></label>
              <input class="amount comma" name="amount" type="text"/>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="submit" class="btn btn-secondary m-0" onclick="withdrawWallet(event)">برداشت</button>
      </div>
    </div>
  </div>
</div>
