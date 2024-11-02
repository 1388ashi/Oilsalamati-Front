<script>

  const provinces = @json($provinces);

  function appendCities(event, selectedCityId = null) {

    const provinceSelect = $(event.target);
    const provinceId = provinceSelect.val();
    const province = provinces.find((p) => p.id == provinceId);
    const cities = province.cities;

    let options = '';
    let selected = null;

    cities.forEach(city => {
      if (selectedCityId != null && city.id == selectedCityId) {
        selected = 'selected';
      }else {
        selected = '';
      }
      options += `<option value="${city.id}" ${selected}>${city.name}</option>`;
    });
    provinceSelect.closest('form').find('select.city').html(options);
  }

  function submitAddress(evenet, method) {

    const form = $(event.target).closest('.modal-content').find('form');
    const url = form.attr('action');

    $.ajax({
      url: url,
      type: method,
      data: {
        city: form.find('.city').val(),  
        first_name: form.find('.first-name').val(),  
        last_name: form.find('.last-name').val(),  
        address: form.find('.address').val(),  
        postal_code: form.find('.postal-code').val(),  
        mobile: form.find('.mobile').val(),
      },
      headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      },
      success: (response) => {  
        popup('موفق', 'success', response.message);
        updateAddressBox(method, response.data.address);
      },  
      error: (error) => {  
        popup('خطا', 'error', error.message);
      }  
    });

  }

  function updateAddressBox(method, address) {

    const addressBox = method === 'PUT' ? $('#AddressBox-' + address.id) : $('#ExampleAddressBox').clone();

    addressBox.find('.address-detail').text(address.address);
    addressBox.find('.address-receiver').text(address.first_name +' '+ address.last_name +' - '+ address.mobile);
    addressBox.find('.address-mobile').text(address.mobile);
    addressBox.find('.address-postal-code').text(address.postal_code);

    if (method === 'POST') {
      addressBox.attr('id', 'AddressBox-' + address.id);
      addressBox.removeClass('d-none');
      addressBox.find('.edit-btn').attr('data-bs-target', '#EditAddressModal-' + address.id);
      addressBox.find('.delete-btn').attr('data-address-id', address.id);
      $('#AddressSection').append(addressBox);
      generateEditFormModalForNewAddress(address);
    }

  }

  function generateEditFormModalForNewAddress(address) {  

    $('#addNewAddressModal').modal('hide');  
    const clonedModal = $('#addNewAddressModal').clone();  
    const updateUrl = @json(route('customer.addresses.index')) + '/' + address.id; 
    const province = provinces.find((p) => p.id == address.city.province_id);

    clonedModal.removeAttr('role');  
    clonedModal.removeAttr('style');  

    clonedModal.attr('id', 'EditAddressModal-' + address.id);  
    clonedModal.attr('aria-labelledby', 'EditAddressModal' + address.id + 'Label');  

    clonedModal.find('.modal-title').attr('id', 'EditAddressModal-' + address.id + 'Label');  
    clonedModal.find('.form').attr('id', 'UpdateAddressForm-' + address.id);  
    clonedModal.find('.form').attr('action', updateUrl);  
    clonedModal.find('.first-name').val(address.first_name);  
    clonedModal.find('.last-name').val(address.last_name);  
    clonedModal.find('.mobile').val(address.mobile);  
    clonedModal.find('.postal-code').val(address.postal_code);  
    clonedModal.find('.address').val(address.address);  
    clonedModal.find('.submit-btn span').text('ذخیره آدرس');  

    appendProvincesToNewAddressForm('EditAddressModal-' + address.id, address.city.province_id);
    appendCitiesToNewAddressForm('EditAddressModal-' + address.id, address, province.cities);

    clonedModal.find('.province').on('change', function(event) {  
      appendCities(event, address.city_id); 
    });  

    $('#address').append(clonedModal);  
  }  

  function appendProvincesToNewAddressForm(addressModalId, selectedProvinceId = null) {

    let options = '';
    let selected = null;

    if (!selectedProvinceId) {
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

  function appendCitiesToNewAddressForm(addressModalId, address, cities) {

    let citiesOption = '';
    let selected = null;

    cities.forEach(city => {

      if (city.id == address.city_id) {
        selected = 'selected';
      }else {
        selected = '';
      }

      citiesOption += `<option value="${city.id}" ${selected}>${city.name}</option>`;
    });

    $('#' + addressModalId).find('select.city').html(citiesOption);
  }

  function deleteAddress(event) {
    Swal.fire ({  
      title: "آیا مطمئن هستید؟",  
      text: "بعد از حذف این آدرس دیگر قابل بازیابی نخواهد بود!",  
      icon: "warning",  
      confirmButtonText: 'حذف کن',  
      showDenyButton: true,  
      denyButtonText: 'انصراف',  
    }).then((result) => {  
      if (result.isConfirmed) {  
        const btn = $(event.target);
      $.ajax({
        url: @json(route('customer.addresses.index')) + '/' + btn.data('address-id'),
        type: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        success: (response) => {  
          popup('موفق', 'success', 'آدرس با موفقیت حذف شد');
          $('#AddressBox-' + btn.data('address-id')).remove();
        },  
        error: (error) => {  
          popup('خطا', 'error', 'عملیات حذف با مشکل مواجه شد');
        }  
      });
      } else if (result.isDenied) {  
        popup('', 'info', 'عملیات حذف نادیده گرفته شد');
      }  
    });
  }
</script>