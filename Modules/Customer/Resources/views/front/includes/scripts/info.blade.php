<script>
  function editProfile(event) {

    const form = $(event.target).closest('.modal').find('form');

    $.ajax({
      url: form.attr('action'),
      type: form.attr('method'),
      data: {
        first_name: form.find('.first-name').val(),  
        last_name: form.find('.last-name').val(),  
        mobile: form.find('.mobile').val(),
        card_number: form.find('.card-number').val(),
      },
      headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      },
      success: (response) => { 
        popup('موفق', 'success', response.message);
        updateInfoBox(response.data.customer);
      },  
      error: (error) => {  
        showErrorMessages(error);
      }  
    });

  }

  function updateInfoBox(customer) {
    const infoBox = $('#info');
    const fullName = customer.first_name +' '+ customer.last_name;
    infoBox.find('.info-full-name').text(fullName);
    infoBox.find('.info-mobile').text(customer.mobile);
    infoBox.find('.info-card-number').text(customer.card_number);
    $('.profile-detail .full-name').text(fullName);
  }


</script>