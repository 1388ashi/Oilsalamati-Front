<div class="modal fade " id="increment-wallet-balance-modal" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content modal-content-demo">
        <div class="modal-header">
          <p class="modal-title">افزایش موجودی کیف پول</p>
          <button aria-label="Close" class="close" data-dismiss="modal"><span
                    aria-hidden="true">×</span></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.customers.deposit') }}" method="post">

            @csrf

            <input type="hidden" name="customer_id" value="{{ $customer->id }}">

            <div class="row">

              <div class="col-12">
                <div class="form-group">
                  <label for="amount">مبلغ (تومان) :<span class="text-danger">&starf;</span></label>
                  <input type="text" class="form-control comma" name="amount" required value="{{ old('amount') }}">
                </div>
              </div>

              <div class="col-12">
                <div class="form-group">
                  <label for="description"> توضیحات: </label>
                  <textarea id="description" class="form-control" rows="2" name="description">{{ old('description') }}</textarea>
                </div>
              </div>

            </div>

            <div class="modal-footer justify-content-center">
              <button class="btn btn-success" type="submit">افزایش موجودی</button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>