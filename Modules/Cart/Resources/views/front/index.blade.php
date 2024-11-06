@extends('front.layouts.master')

@section('title')
	<title>سبد خرید</title>
@endsection

@section('body_class') checkout-page checkout-style1-page @endsection

@section('content')

@include('cart::front.includes.breadcrumb')
<x-alert-danger/>

<div class="container">
  <div class="row">
    <div class="col-12 col-sm-12 col-md-12 col-lg-12 mt-5">

      @include('cart::front.includes.nav-tabs')

      <form id="StoreOrderForm" class="d-none" action="{{ route('customer.orders.store') }}" method="POST">@csrf</form>

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

  const provinces = @json($provinces);  
  const cities = @json($cities);  
  const addresses = @json($user->addresses);

  const clonedAddressModal = $('#addNewAddressModal').clone();

  let allShippings = [];
  let allCarts = [];
  let couponCode = null;

  $(document).ready(function() {  

    allCarts = @json($user->carts);

    $('.qtyBtn').on('click', function() {

      const input = $(this).closest('td').find('.cart-qty-input');
      const cartId = input.data('cart-id');
      let currentValue = parseInt(input.val());
      let newVal;

      if ($(this).is(".plus")) {  
        newVal = currentValue + 1;  
      } else if (currentValue > 1) {  
        newVal = currentValue - 1;  
      }else {
        Swal.fire ({  
          title: 'حداقل تعداد 1 می باشد',
          text: 'آیا تمایل دارید محصول را از سبد حذف کنید',
          icon: "warning",  
          confirmButtonText: 'حذف کن',  
          showDenyButton: true,  
          denyButtonText: 'انصراف',  
          dangerMode: true,  
        }).then((result) => {  
          if (result.isConfirmed) {  
            document.getElementById('delete-cart-' + cartId).submit();  
            recalculateShipping();
            Swal.fire({  
              icon: "success",  
              text: "آیتم با موفقیت حذف شد."  
            });  
          } 
        });
         
        return;
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

          showCartUpdateMessageAlert('success', response.message);
          updateCartOrderDetail(response.data.carts);

          const newCarts = response.data.carts;
          const changedCart = newCarts.find((c) => c.id == cartId);
          
          updateTablePrices(changedCart);
          recalculateShipping();

          if (changedCart.quantity != newVal) {
            input.val(changedCart.quantity);
          }

          allCarts = response.data.carts;
        },  
        error: (xhr, status, error) => {  
          console.log(xhr);
          showCartUpdateMessageAlert('error', xhr)
        }  
      });
    });

    $('#coupon-code-button').on('click', function() {
      applyCouponCode();
    });

    $('#steps1-btnNext').on('click', function() {

      activeTabButton($('.nav-item:eq(1)'));
      activeNewTab($('#steps2'), $('#steps1'));
      appendProvincesToAddressForm('addNewAddressModal', provinces);  
      addresses.forEach(address => {
        let provinceToSelect = address.city.province_id;
        let modalId = 'editAddressModal-' + address.id;
        appendProvincesToAddressForm(modalId, provinces, true, provinceToSelect);
        appendCitiesToExistsAddressForm(modalId, address);
      });

    });

    $('#steps2-btnPrevious').on('click', function() {
      activeTabButton($('.nav-item:eq(0)'));
      activeNewTab($('#steps1'), $('#steps2'));
    });

    $('#steps2-btnNext').on('click', function() {

      const isValidate = validateAddressAndShipping();

      if (isValidate) {
        activeTabButton($('.nav-item:eq(2)'));
        activeNewTab($('#steps3'), $('#steps2'));
        updateFinalCartOrderDetail();
      }

    });

    $('#steps3-btnPrevious').on('click', function() {
      activeTabButton($('.nav-item:eq(1)'));
      activeNewTab($('#steps2'), $('#steps3'));
    });

    $('input[name=address_id]').on('click', function() {
      checkShippableShipping($(event.target).data('url'));
    });

    $('#cartCheckout').on('click', function() {

      const isValide = validateOrderTypeAndPayType();
      if (isValide) {
        submitForm();
      }

    });

  });

  function showCartUpdateMessageAlert(icon, text) {
    Swal.fire ({  
      text: text,  
      icon: icon,  
      confirmButtonText: 'بستن',  
    }); 
  }

  function updateCartOrderDetail(carts) {

    let sumTotalPrice = 0;
    let sumTotalDiscount = 0;
    let sumTotalPriceWithDiscount = 0;

    carts.forEach(cart => {
      sumTotalPrice += cart.quantity * cart.price;
      sumTotalDiscount += cart.quantity * cart.discount_price;
      sumTotalPriceWithDiscount += cart.quantity * (cart.price - cart.discount_price);
    });

    $('#SumPrice').text(sumTotalPrice.toLocaleString() + ' ' + 'تومان');
    $('#SumDiscount').text(sumTotalDiscount.toLocaleString() + ' ' + 'تومان');
    $('#SumPriceWithDiscount').text(sumTotalPriceWithDiscount.toLocaleString() + ' ' + 'تومان');

  }

  function updateTablePrices(cart) {
    const tr = $('#CartsTable').find(`#Cart-${cart.id}`);
    tr.find('.unit-price').text(cart.price.toLocaleString());
    tr.find('.unit-discount').text(cart.discount_price.toLocaleString());
    tr.find('.price').text(cart.cart_price_amount.toLocaleString());
  }

  function preventChange(event) {  
    event.preventDefault();  
    event.stopPropagation();  
    alert("تغییر مقدار این فیلد مجاز نیست.");  
  }  

  function activeTabButton(elementToToggle) {  

    const classesToCheck = ["active", "onactive"];  
    const elementToRemoveClass = $('.nav-tabs .nav-item.onactive');

    let allClassesPresent = true;  

    $.each(classesToCheck, function(index, className) {  
      if (!elementToToggle.hasClass(className)) {  
        allClassesPresent = false;  
      }  
    });  

    if (allClassesPresent) {  
      elementToToggle.removeClass(classesToCheck.join(" "));  
    } else {  
      elementToToggle.addClass(classesToCheck.join(" "));   
    }  

    if (elementToRemoveClass.hasClass("onactive")) {  
      elementToRemoveClass.removeClass("onactive");  
    }  
  }  

  function activeNewTab(tabToShow, tabToClose) {

    tabToShow.addClass('active');
    tabToClose.removeClass('active');

    if (tabToShow.attr('id') == 'steps3') {
      tabToShow.addClass('show');
    }
    
    if (tabToClose.attr('id') == 'steps3') {
      tabToShow.removeClass('show');
    }
    
  }

  function appendProvincesToAddressForm(addressModalId, provinces, isUpdate = false, selectedProvinceId = null) {

    let options = '';
    let selected = null;

    if (!isUpdate) {
      options += '<option value="">استان را انتخاب کنید</option>';
    }

    provinces.forEach(province => {

      if (selectedProvinceId != null && province.id == selectedProvinceId) {
        selected = 'selected';
      }else {
        selected = '';
      }

      options += `<option value="${province.id}" ${selected}>${province.name}</option>`;
    });

    $('#' + addressModalId).find('select.province').html(options);

  }

  function appendCities(event, selectedCityId = null) {

    const provinceSelect = $(event.target);
    const provinceId = provinceSelect.val();
    const thisCities = cities.filter((c) => c.province_id == provinceId);
    
    let options = '';
    let selected = null;

    thisCities.forEach(city => {

      if (selectedCityId != null && city.id == selectedCityId) {
        selected = 'selected';
      }else {
        selected = '';
      }

      options += `<option value="${city.id}" ${selected}>${city.name}</option>`;
    });

    provinceSelect.closest('form').find('select.city').html(options);
  }

  function openEditAddressModal(event) {
    const modalId = $(event.target).data('target-modal-id');
    $('#' + modalId).modal('show');
  }

  function appendCitiesToExistsAddressForm(modalId, address) {
    
    let citiesOption = '';
    let selected = null;

    let selectedCity = cities.find((c) => c.id == address.city_id);
    let sameProviceCities = cities.filter((c) => c.province_id == selectedCity.province_id);

    sameProviceCities.forEach(city => {

      if (city.id == selectedCity.id) {
        selected = 'selected';
      }else {
        selected = '';
      }

      citiesOption += `<option value="${city.id}" ${selected}>${city.name}</option>`;
    });

    $('#' + modalId).find('select.city').html(citiesOption);
  }

  function submitAddress(evenet, method) {

    const form = $(event.target).closest('.modal-content').find('form');
    const data = getAddressData(form);

    $.ajax({
      url: form.attr('action'),
      type: method,
      data: data,
      headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      },
      success: (response) => {  
        showAddressPopup('success', response.message);
        updateAddressBox(method, response.data.address);
      },  
      error: (error) => {  
        showAddressPopup('error', error);
      }  
    });
  }

  function getAddressData(form) {

    const data = {  
      city: form.find('.city').val(),  
      first_name: form.find('.first_name').val(),  
      last_name: form.find('.last_name').val(),  
      address: form.find('.address').val(),  
      postal_code: form.find('.postal_code').val(),  
      mobile: form.find('.mobile').val(),  
    };

    return data; 
  }

  function showAddressPopup(alertType, message) {
    Swal.fire ({  
      text: message,
      icon: alertType,  
      confirmButtonText: 'بستن',  
    });
  }

  function updateAddressBox(method, address) {

    const detail = address.city.province.name +' - '+ address.city.name +' - '+ address.address;
    const receiver = address.first_name +' '+ address.last_name +' - '+ address.mobile;
    const postalCode = address.postal_code;

    const addressBox = method === 'PUT' ? $('#AddressBox-' + address.id) : $('#AddressBox').clone();

    addressBox.find('.address-detail').text(detail);
    addressBox.find('.address-receiver').text(receiver);
    addressBox.find('.postal-code').text('کد پستی : ' + postalCode);

    if (method === 'POST') {
      addressBox.attr('id', 'AddressBox-' + address.id);
      addressBox.find('input').attr('id', 'formcheckoutRadio-' + address.id);
      addressBox.find('input').val(address.id);
      addressBox.find('label.address-detail').attr('for', 'formcheckoutRadio-' + address.id);
      addressBox.find('.edit-btn').attr('data-target-modal-id', 'editAddressModal-' + address.id);
      addressBox.find('.delete-btn').attr('data-delete-address-url', @json(route('customer.addresses.index')) + '/' + address.id);
      addressBox.find('.delete-btn').attr('data-address-id', address.id);
      addressBox.removeClass('d-none');
      $('#AddressSection').append(addressBox);
      generateEditFormModalForNewAddress(address);
    }

  }

  function confrimDeletingAddress(event) {
    Swal.fire ({  
      title: "آیا مطمئن هستید؟",  
      text: "بعد از حذف این آدرس دیگر قابل بازیابی نخواهد بود!",  
      icon: "warning",  
      confirmButtonText: 'حذف کن',  
      showDenyButton: true,  
      denyButtonText: 'انصراف',  
    }).then((result) => {  
      if (result.isConfirmed) {  
        deleteAddress(event);
      } else if (result.isDenied) {  
        Swal.fire({  
          icon: "info",  
          text: "عملیات حذف نادیده گرفته شد."  
        });  
      }  
    });  
  } 

  function deleteAddress(event) {
    const btn = $(event.target);
    $.ajax({
      url: btn.data('delete-address-url'),
      type: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      },
      success: (response) => {  
        Swal.fire({  
          icon: "success",  
          text: "آدرس با موفقیت حذف شد."  
        });  
        $('#AddressBox-' + btn.data('address-id')).remove();
      },  
      error: (error) => {  
        Swal.fire({  
          icon: "error",  
          text: "عملیات حذف با مشکل مواجه شد."  
        });  
      }  
    });
  }

  function generateEditFormModalForNewAddress(address) {  

    const clonedModal = clonedAddressModal.clone();  
    const updateUrl = @json(route('customer.addresses.index')) + '/' + address.id; 

    clonedModal.attr('id', 'editAddressModal-' + address.id);  
    clonedModal.attr('aria-labelledby', 'editAddressModal' + address.id + 'Label');  

    clonedModal.find('.modal-title').attr('id', 'editAddressModal' + address.id + 'Label');  
    clonedModal.find('.form').attr('id', 'UpdateAddressForm-' + address.id);  
    clonedModal.find('.form').attr('action', updateUrl);  
    clonedModal.find('.first_name').val(address.first_name);  
    clonedModal.find('.last_name').val(address.last_name);  
    clonedModal.find('.mobile').val(address.mobile);  
    clonedModal.find('.postal_code').val(address.postal_code);  
    clonedModal.find('.address').val(address.address);  

    $('#steps2').append(clonedModal);  
    appendProvincesToAddressForm('editAddressModal-' + address.id, provinces, true, address.city.province_id);
    appendCitiesToExistsAddressForm('editAddressModal-' + address.id, address);

    clonedModal.find('.province').on('change', function(event) {  
      appendCities(event, address.city_id); 
    });  

    $('#addNewAddressModal').modal('hide');
    
  }  

  function checkShippableShipping(url) {
    $.ajax({
      url: url,
      type: 'GET',
      success: (response) => {
        allShippings = response.data.shippings;
        updateShippingBox(allShippings);
      },  
      error: (error) => {  
        Swal.fire ({  
          text: error,
          icon: 'error',  
          confirmButtonText: 'بستن',  
        });
      }  
    });
  }

  function updateShippingBox(shippings) {

    $('#ShippingSection').empty();

    shippings.forEach(shipping => {

      const shippingBox = $('#ShippingBox').clone();

      shippingBox.find('img').attr('src', shipping.logo.url);
      shippingBox.find('.shipping-price').text(shipping.calculated_response.shipping_amount.toLocaleString() +' تومان');

      shippingBox.find('input[name=shipping_id]')
        .attr('id', 'formcheckoutRadio-' + shipping.id)
        .val(shipping.id);
      
      shippingBox.find('label')
        .attr('for', 'formcheckoutRadio-' + shipping.id)
        .text(shipping.name);

      if (shipping.description) {
        shippingBox.find('.customRadio').append(`<p class="text-muted">${shipping.description}</p>`);
      }

      shippingBox.removeClass('d-none');
      shippingBox.removeAttr('id');

      $('#ShippingSection').append(shippingBox);
    }); 
  }

  function validateAddressAndShipping() {

    const isAnyAddressSelected = $('input[name="address_id"]:checked').length > 0;
    const isAnyShippingSelected = $('input[name="shipping_id"]:checked').length > 0;

    if (!isAnyAddressSelected) {
      generateWarningPopup('انتخاب آدرس الزامی است!');
      return false;
    }

    if (!isAnyShippingSelected) {
      generateWarningPopup('انتخاب نوع حمل و نقل الزامی است!');
      return false;
    }

    return true;
  }

  function generateWarningPopup(message) {
    Swal.fire ({  
      title: message,
      icon: 'warning',  
      confirmButtonText: 'بستن',  
    });
  }

  function applyCouponCode() {
    const input = $('#coupon-code');
    couponCode = input.val();

    Swal.fire({  
      icon: "success",  
      text: "کد تخفیف با موفقیت اعمال شد."  
    }); 

  }

  function recalculateShipping() {
    const input = $('input[name="address_id"]:checked');
    if (input.length > 0) {
      checkShippableShipping(input.data(url));
    }
  }

  function getCartTotalPrice() {

    let totalDiscountAmount = 0;
    let totalCartsPrice = 0;
    let finalPrice = 0;

    allCarts.forEach(cart => {
      totalDiscountAmount += cart.quantity * cart.discount_price;
      totalCartsPrice += cart.quantity * cart.price;
      finalPrice += (cart.price - cart.discount_price) * cart.quantity;
    });

    return {
      totalDiscountAmount: parseInt(totalDiscountAmount),
      totalCartsPrice: parseInt(totalCartsPrice),
      finalPrice: parseInt(finalPrice)
    };
  }

  function getShippingPrice() {

    const shippingInput = $('input[name="shipping_id"]:checked');
    const shippingId = shippingInput.val();
    const shipping = allShippings.find((shipping) => shipping.id == shippingId);

    return parseInt(shipping.calculated_response.shipping_amount);
  }

  function getCouponPrice() {
    return 0;
  }

  function getTotalInvoiceAmount() {

    const cartPrice = getCartTotalPrice().finalPrice;
    const shippingAmount = getShippingPrice();
    const discountOnCoupon = getCouponPrice();

    return parseInt(cartPrice + shippingAmount - discountOnCoupon);
  }

  function updateFinalCartOrderDetail() {

    const orderDetailBox = $('#FinalCartOrderDetail');
    const cartsPrices = getCartTotalPrice();

    orderDetailBox.find('.total-price-amount').text(cartsPrices.totalCartsPrice.toLocaleString());
    orderDetailBox.find('.total-discount-amount').text(cartsPrices.totalDiscountAmount.toLocaleString());
    orderDetailBox.find('.coupon-amount').text(getCouponPrice().toLocaleString());
    orderDetailBox.find('.shipping-amount').text(getShippingPrice().toLocaleString());
    orderDetailBox.find('.total').text(getTotalInvoiceAmount().toLocaleString());

  }

  function validateOrderTypeAndPayType() {

    const isAnyOrderTypeSelected = $('input.order-type-input:checked').length > 0;
    const isAnyPayTypeSelected = $('input.pay-type-input:checked').length > 0;
    const isAnyDriverSelected = $('input.driver-input:checked').length > 0;

    if (!isAnyOrderTypeSelected) {
      generateWarningPopup('انتخاب نوع سفارش الزامی است!');
      return false;
    }

    if (!isAnyPayTypeSelected) {
      generateWarningPopup('انتخاب نوع پرداخت الزامی است!');
      return false;
    }

    if (['both', 'gateway'].includes($('input.pay-type-input:checked').val()) && !isAnyDriverSelected) {
      generateWarningPopup('انتخاب درگاه الزامی است!');
      return false;
    }

    return true;
  }

  function submitForm() {

    const shippingId = $('input[name=shipping_id]:checked').val();
    const addressId = $('input[name=address_id]:checked').val();
    const paymentDriver = $('input.driver-input:checked').val();
    const payType = $('input.pay-type-input:checked').val();

    $('#StoreOrderForm').append(makeInput('shipping_id', shippingId));
    $('#StoreOrderForm').append(makeInput('address_id', addressId));
    $('#StoreOrderForm').append(makeInput('pay_type', payType));

    if (paymentDriver != null) {
      $('#StoreOrderForm').append(makeInput('payment_driver', paymentDriver));
    }

    if (couponCode != null) {
      $('#StoreOrderForm').append(makeInput('coupon_code', couponCode));
    }

    $('#StoreOrderForm').submit();

  }

  function makeInput(name, value) {
    return $(`<input type="hidden" name="${name}" value="${value}"/>`);
  }

</script>
@endsection