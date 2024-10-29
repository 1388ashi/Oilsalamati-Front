@extends('front.layouts.master')

@section('title')
	<title>سبد خرید</title>
@endsection

@section('body_class') checkout-page checkout-style1-page @endsection
 
@section('content')

@include('cart::front.includes.breadcrumb')

<div class="container">
  <div class="row">
    <div class="col-12 col-sm-12 col-md-12 col-lg-12 mt-5">

      @include('cart::front.includes.nav-tabs')

      <div class="tab-content checkout-form">

        @include('cart::front.includes.cart')
        @include('cart::front.includes.shipping')
        @include('cart::front.includes.payment')

      </div>

    </div>
  </div>
</div>

@endsection

@section('scripts')

<script>
  $(document).ready(function () {
    // Add active class to the current list tem (highlight it)
    let checkoutList = document.getElementById("nav-tabs");
    let checkoutItems = checkoutList.getElementsByClassName("nav-item");
    for (let i = 0; i < checkoutItems.length; i++) {
      checkoutItems[i].addEventListener("click", function () {
        let current = document.getElementsByClassName("onactive");
        current[0].className = current[0].className.replace(
          " onactive",
          ""
        );
        this.className += " onactive";
      });
    }

    // Nav next/prev
    $(".btnNext").click(function () {
      const nextTabLinkEl = $(".nav-tabs .active")
        .closest("li")
        .next("li")
        .find("a")[0];
      const nextTab = new bootstrap.Tab(nextTabLinkEl);
      nextTab.show();
    });
    $(".btnPrevious").click(function () {
      const prevTabLinkEl = $(".nav-tabs .active")
        .closest("li")
        .prev("li")
        .find("a")[0];
      const prevTab = new bootstrap.Tab(prevTabLinkEl);
      prevTab.show();
    });
  });
</script>

<script>

  $(document).ready(function() {

    $('.qtyBtn').on('click', function() {

      const input = $(this).closest('td').find('.cart-qty-input');
      const cartId = input.data('cart-id');
      let currentValue = parseInt(input.val());
      let newVal;

      if ($(this).is(".plus")) {  
        newVal = currentValue + 1;  
      } else if (currentValue > 1) {  
        newVal = currentValue - 1;  
      }  

      input.val(newVal);  

      $.ajax({
        url: `{{ route('cart.index') }}` + '/' + cartId,
        type: 'PUT',
        data: {
          quantity: newVal,
        },
        headers: {
          'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        success: (response) => {  

          const newCarts = response.data.carts;
          const changedCart = newCarts.find((c) => c.id == cartId);
          const tr = $('#CartsTable').find(`#Cart-${cartId}`);

          tr.find('.unit-price').text(changedCart.price.toLocaleString());
          tr.find('.unit-discount').text(changedCart.discount_price.toLocaleString());
          tr.find('.price').text(changedCart.cart_price_amount.toLocaleString());

          if (changedCart.quantity != newVal) {
            input.val(changedCart.quantity);
          }

        },  
        error: (xhr, status, error) => {  
          console.error(xhr);  
        }  
      });
    });
  });

</script>
@endsection