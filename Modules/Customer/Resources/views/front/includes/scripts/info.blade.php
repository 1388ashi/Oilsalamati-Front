<script>
  function editProfile(event) {

    const form = $('#EditProfileForm');

      console.log($('.birth-date-1').val());
      console.log($('.birth-date-2').val());
return;
    $.ajax({
      url: form.attr('action'),
      type: form.attr('method'),
      data: {
        first_name: form.find('.first-name').val(),  
        last_name: form.find('.last-name').val(),  
        mobile: form.find('.mobile').val(),
        card_number: form.find('.card-number').val(),
        email: form.find('.email').val(),
        national_code: form.find('.national-code').val(),
        gender: form.find('.gender').val(),
        birth_date: form.find('.birth-date').val(),
      },
      headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      },
      success: (response) => { 
        popup('موفق', 'success', response.message);
        const customer = response.data.customer;
        let fullName = '';
        if (customer.first_name) {
          fullName += customer.first_name;
        } 
        if (customer.last_name) {
          fullName = fullName +' '+ customer.last_name;
        } 
        $('.profile-detail .full-name').text(fullName);
      },  
      error: (error) => {  
        showErrorMessages(error);
      }  
    });

  }

</script>

<script>

    const dtp1Instance = new mds.MdsPersianDateTimePicker(document.getElementById('birth-date'), {
        targetTextSelector: '#birth-date',
        targetDateSelector: '#birth-date-hide',
        toDate:true,
        dateFormat: 'yyyy-MM-dd',
        textFormat: 'yyyy-MM-dd',
        groupId: 'dateRangeSelector1',
    });

</script>