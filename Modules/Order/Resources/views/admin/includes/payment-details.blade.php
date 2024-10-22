@php
  $invoices = $order->invoices->filter(fn($invoice) => $invoice->status === 'success');
@endphp
@if ($invoices->isNotEmpty())
  <div class="row" style="margin-block: 30px;">
    <p class="header fs-20 text-center border border-bottom-0 w-100 mb-0 font-weight-bold">اطلاعات پرداخت</p>
    <div class="col-12">
      <div class="row">
        <div class="table-responsive">
          <div class="dataTables_wrapper dt-bootstrap4 no-footer">
            <div class="row">
              <table class="table table-striped table-bordered text-nowrap text-center">
                <thead>
                <tr>
                  <th colspan="1">ردیف</th>
                  <th colspan="1">شناسه</th>
                  <th colspan="1">زمان</th>
                  <th colspan="1">کد رهگیری</th>
                  <th class="p-0" colspan="4">
                    <div class="my-2">کیف پول</div>
                    <div class="w-100 border-bottom"></div>
                    <div class="col-12">
                      <div class="row">
                        <div class="col-4 py-1">هدیه</div>
                        <div class="col-4 py-1 border border-top-bottom-0">اصلی</div>
                        <div class="col-4 py-1">مجموع</div>
                      </div>
                    </div>
                  </th>
                  <th colspan="2">پرداختی از درگاه</th>
                  <th colspan="2">مبلغ پرداختی</th>
                </tr>
                </thead>
                <tbody>

                @foreach ($invoices as $invoice)

                  @php
                    $payment = $invoice->payments->first();
                    $gatewayLabel = $payment->gateway_label ?? 'نامشخص';
                    $trackingCode = $payment->tracking_code ?? '-';

                    $giftWalletAmount = $invoice->gift_wallet_amount;
                    $mianWalletAmount = $invoice->wallet_amount - $invoice->gift_wallet_amount;
                    $totalWalletAmount = $invoice->wallet_amount;

                  @endphp

                  <tr>
                    <td colspan="1">{{ $loop->iteration }}</td>
                    <td colspan="1">{{ $invoice->id }}</td>
                    <td colspan="1">{{ verta($invoice->created_at)->format('Y/m/d H:i:s') }}</td>
                    <td colspan="1">{{ $trackingCode }}</td>
                    <td class="p-0" colspan="4">
                      <div class="col-12 m-0">
                        <div class="row">
                          <div class="col-4 py-1">{{ number_format($giftWalletAmount) }}</div>
                          <div class="col-4 py-1 border border-top-bottom-0">{{ number_format($mianWalletAmount) }}</div>
                          <div class="col-4 py-1">{{ number_format($totalWalletAmount) }}</div>
                        </div>
                      </div>
                    </td>
                    <td colspan="2">{{ number_format($invoice->amount - $invoice->wallet_amount) }}</td>
                    <td colspan="2">{{ number_format($invoice->amount) }}</td>
                  </tr>

                @endforeach
                <tr class="bg-dark text-white fs-17">
                  <td colspan="4">جمع کل :</td>
                  <td colspan="4" class="font-weight-bold p-0">
                    <div class="col-12">
                      <div class="row">
                        <div class="col-4 py-1">{{ number_format($order->paid_by_wallet_gift_balance) }}</div>
                        <div
                          class="col-4 py-1 border border-top-bottom-0">{{ number_format($order->paid_by_wallet_main_balance) }}</div>
                        <div
                          class="col-4 py-1">{{ number_format($order->paid_by_wallet_main_balance + $order->paid_by_wallet_gift_balance) }}</div>
                      </div>
                    </div>
                  </td>
                  <td colspan="2"
                      class="font-weight-bold">{{ number_format($order->total_invoices_amount - $order->paid_by_wallet_gift_balance - $order->paid_by_wallet_main_balance) }}</td>
                  <td colspan="2" class="font-weight-bold">{{ number_format($order->total_invoices_amount) }}</td>
                </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endif
