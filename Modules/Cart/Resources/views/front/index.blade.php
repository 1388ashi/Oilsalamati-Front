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

{{-- <form action="{{ route('cart.add', ['variety' => 530]) }}" method="POST">
  @csrf
  <input type="hidden" name="quantity" value="1">
  <button class="btn btn-primary">border</button>
</form> --}}

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

  let provinces = [];  
  let cities = [];  
  const addresses = @json($user->addresses);
  const clonedAddressModal = $('#addNewAddressModal').clone();  

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

          if (changedCart.quantity != newVal) {
            input.val(changedCart.quantity);
          }

        },  
        error: (xhr, status, error) => {  
          console.log(xhr);
          showCartUpdateMessageAlert('error', xhr)
        }  
      });
    });

    $('#steps1-btnNext').on('click', function() {

      activeTabButton($('.nav-item:eq(1)'));
      activeNewTab($('#steps2'), $('#steps1'));
      getAllCities();
      getAllProvinces().then(allProvinces => {  
        appendProvincesToAddressForm('addNewAddressModal', allProvinces);  
        addresses.forEach(address => {
          let provinceToSelect = address.city.province_id;
          let modalId = 'editAddressModal-' + address.id;
          appendProvincesToAddressForm(modalId, allProvinces, true, provinceToSelect);
          appendCitiesToExistsAddressForm(modalId, address);
        });
      }).catch(error => {  
        showProvinceAjaxErrorAlert(error)
      });

    });

    $('#steps2-btnPrevious').on('click', function() {
      activeTabButton($('.nav-item:eq(0)'));
      activeNewTab($('#steps1'), $('#steps2'));
    });

    $('#steps2-btnNext').on('click', function() {
      activeTabButton($('.nav-item:eq(2)'));
      activeNewTab($('#steps3'), $('#steps2'));
    });

    $('.shipping-radio').on('input', function() {
      console.log('asda');
      
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

  function getAllProvinces() {

    if (provinces.length !== 0) {  
      return Promise.resolve(provinces);
    } 

    return $.ajax({  
      url: '{{ route("admin.provinces.getAllProvinces") }}',  
      type: 'GET',  
      success: (response) => {  
        provinces = response.data.provinces;
      },  
      error: (error) => {  
        showProvinceAjaxErrorAlert(error);
        return [];
      }  
    }).then(() => provinces);

  }

  function showProvinceAjaxErrorAlert(error) {
    Swal.fire ({  
      title: 'بارگزاری استان ها با خطا مواجه شد!',
      text: error,
      icon: "error",  
      confirmButtonText: 'بستن',  
    });
  }

  function getAllCities() {

    if (cities.length != 0) {
      return Promise.resolve(cities);
    }

    return $.ajax({  
      url: '{{ route("admin.cities.getAllCities") }}',  
      type: 'GET',  
      success: (response) => {  
        cities = response.data.cities;
      },  
      error: (error) => {  
        showCitiesAjaxErrorAlert(error);  
        return [];
      }  
    }).then(() => cities);
  }

  function showCitiesAjaxErrorAlert(error) {
    Swal.fire ({  
      title: 'بارگزاری شهر ها با خطا مواجه شد!',
      text: error,
      icon: "error",  
      confirmButtonText: 'بستن',  
    });
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

</script>
@endsection