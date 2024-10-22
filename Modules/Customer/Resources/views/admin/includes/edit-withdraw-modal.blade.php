@foreach ($withdraws as $withdraw)
    <div class="modal fade" id="edit-withdraw-modal-{{ $withdraw->id }}" style="display: none;" aria-hidden="true">
      <div class="modal-dialog modal-md" role="document">
        <div class="modal-content modal-content-demo">
          <div class="modal-header">
            <p class="modal-title" style="font-size: 20px;">ویرایش برداشت - کد {{ $withdraw->id }}</p>
            <button aria-label="Close" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
          </div>
          <div class="modal-body">
            <form action="{{ route('admin.withdraws.update', $withdraw) }}" method="POST">
              @csrf
              @method('PUT')
              <div class="row">

                <div class="col-12">
                  <div class="form-group">
                    <label for="name" class="control-label">مبلغ (تومان) :</label>
                    <input type="text" class="form-control" name="amount" value="{{ number_format($withdraw->amount) }}" readonly>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-group">
                    <label class="control-label">شماره کارت :</label>
                    <input type="text" class="form-control" name="card_number" value="{{ $withdraw->card_number }}">
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-group">
                    <label class="control-label">کد رهگیری :</label>
                    <input type="text" class="form-control" name="tracking_code" value="{{ $withdraw->tracking_code }}">
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-group">
                    <label for="label" class="control-label"> وضعیت: <span class="text-danger">&starf;</span></label>
                      <select name="status" id="status" class="form-control">
                        @foreach (config('customer.withdraw_statuses') as $name => $label)
                          <option 
                            value="{{ $name }}"
                            {{ $withdraw->status == $name ? 'selected' : null }}>
                            {{ $label }}
                          </option>	
                        @endforeach
                      </select>
                    </label>
                  </div>
                </div>

              </div>

              <div class="modal-footer justify-content-center mt-2">
                <button class="btn btn-warning" type="submit">بروزرسانی</button>
                <button class="btn btn-outline-danger" data-dismiss="modal">انصراف</button>
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>
  @endforeach