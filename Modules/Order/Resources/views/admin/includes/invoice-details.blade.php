<div class="price-box col-md-3 mx-auto" style="flex: 0 0 28%; max-width: 28%;border-radius: 2px;background-color: #f3f4f8b7">
  <div class="d-md-flex justify-content-between">
    <span class=" px-3 mt-5 font-weight-bold"> مبلغ کل با تخفیف:</span>
    <span class=" px-3 mt-5"> {{ number_format($order->total_products_prices_with_discount) }} تومان </span>
  </div>
  <hr>
  <div class="d-md-flex justify-content-between">
    <span class=" px-3  font-weight-bold"> هزینه ارسال:</span>
    <span class=" px-3 ">{{ $order->discount_on_products ? number_format($order->discount_on_products) : 0 }} تومان </span>
  </div>
  <hr>
  <div class="d-md-flex justify-content-between">
    <span class=" px-3  font-weight-bold"> تخفیف کوپن:</span>
    <span class=" px-3 ">
      @if ($order->total_discount_on_orders)
        {{ number_format($order->total_discount_on_orders) }} تومان 
      @else
      ندارد
      @endif
    </span>
  </div>
  <hr>
  <div style="background-color: rgb(40, 167, 69);border-radius: 2px;max-height: 50px;height: 45px;" class="mb-1 d-md-flex justify-content-between align-items-center">
    <span style="font-size: 18px" class="mr-4 text-white">قیمت نهایی:</span>
    <span style="font-size: 18px" class="text-white ml-2">{{number_format($order->total_invoices_amount)}} تومان</span>
  </div>
</div>
