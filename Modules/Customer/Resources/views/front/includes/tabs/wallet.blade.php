<div class="tab-pane fade h-100" id="wallet">
  <div class="banks-card mt-0 h-100">
    <div class="top-sec d-flex-justify-center justify-content-between mb-4">
      <h2 class="mb-0">تراکنش های کیف پول</h2>
      <div class="wallet-transaction-btns">
        <button type="button" class="btn btn-secondary btn-sm radius-16" data-bs-toggle="modal" data-bs-target="#WithdrawWalletModal">برداشت از کیف پول</button>
        <button type="button" class="btn btn-primary btn-sm radius-16" data-bs-toggle="modal" data-bs-target="#DepositWalletModal">افزایش موجودی</button>
      </div>
    </div>

    <div class="table-bottom-brd table-responsive">
      <table class="table align-middle text-center order-table">
        <thead>
          <tr class="table-head text-nowrap">
            <th>ردیف</th>
            <th>شناسه تراکنش</th>
            <th>مبلغ (تومان)</th>
            <th>نوع</th>
            <th>وضعیت</th>
            <th>تاریخ</th>
            <th>ساعت</th>
          </tr>
        </thead>
        <tbody>

          @forelse ($customer->wallet->transactions->sortByDesc('id') as $transaction)
          <tr>
            <td class="fw-bold">{{ $loop->iteration }}</td>
            <td>{{ $transaction->id }}</td>
            <td>{{ number_format(abs($transaction->amount)) }}</td>
            <td>
              @if($transaction->type == 'deposit')
                <span title="نوع" class="badge rounded-pill bg-primary custom-badge">افزایش کیف پول</span>
              @else
                <span title="نوع" class="badge rounded-pill bg-warning custom-badge">برداشت از کیف پول</span>
              @endif
            </td>
            <td>
              @if($transaction->confirmed)
                <span title="وضعیت" class="badge rounded-pill bg-success custom-badge">موفقیت آمیز</span>
              @else
                <span title="وضعیت" class="badge rounded-pill bg-danger custom-badge">خطا</span>
              @endif
            </td>
            <td>{{verta($transaction->created_at)->format('Y/m/d')}}</td>
            <td>{{verta($transaction->created_at)->formatTime()}}</td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="text-center text-danger">آیتمی یافت نشد</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@include('customer::front.includes.wallet.withdraw-modal')
@include('customer::front.includes.wallet.deposit-modal')