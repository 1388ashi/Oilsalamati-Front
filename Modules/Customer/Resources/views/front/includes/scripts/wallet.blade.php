<script>
  function depositWallet(event) {

    const form = $(event.target).closest('.modal').find('form');
    const url = form.attr('action');
    const method = form.attr('method');
    let amount = form.find('.amount').val();

    if (amount == null) {
      popup('اخطار', 'warning', 'مبلغ واریز را وارد کنید');
      return;
    }

    amount = parseInt(amount.replace(/,/g, ''));

    $.ajax({
      url: url,
      type: method,
      data: {
        amount: amount,
      },
      headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      },
      success: (response) => {
        redirectToGatway(response.data.make_response.url);
      },  
      error: (error) => {  
        showErrorMessages(error);
      }  
    });

  }

  function redirectToGatway(url) {
    Swal.fire ({  
      title: "درحال انتقال به درگاه",  
      text: "آیا می خواهید ادامه دهید ؟!",  
      icon: "info",  
      confirmButtonText: 'رفتن به درگاه',  
      showDenyButton: true,  
      denyButtonText: 'انصراف',  
    }).then((result) => {  
      if (result.isConfirmed) {  
        window.location.replace(url);
      } else if (result.isDenied) {  
        popup('', 'info', 'عملیات حذف نادیده گرفته شد');
      }  
    });
  }

  function withdrawWallet(event) {

    const form = $(event.target).closest('.modal').find('form');
    const url = form.attr('action');
    const method = form.attr('method');
    let amount = form.find('.amount').val();

    if (amount == null) {
      popup('اخطار', 'warning', 'مبلغ برداشت را وارد کنید');
      return;
    }

    amount = parseInt(amount.replace(/,/g, ''));
    
    $.ajax({  
      url: url,  
      type: method,  
      data: {  
        amount: amount,  
      },  
      headers: {  
        'X-CSRF-TOKEN': "{{ csrf_token() }}"  
      },  
      success: (response) => {  
        popup('عملیات موفق', 'success', response.message);
        updateWalletBalance(amount);
      },  
      error: (error) => {   
        showErrorMessages(error);
      }  
    });
  }
  
  function updateWalletBalance(amount) {
    const oldBalance = $('.wallet-balance').data('balance');
    const newBalance = oldBalance - amount;

    $('.wallet-balance')
      .text(newBalance.toLocaleString())
      .attr('data-balance', newBalance);
  }

  function selectGatway(event) {  
    const gatwayImages = $('#GatwaySection').find('.gatway-img');  
    gatwayImages.each(function() {  
      $(this).removeClass('selected-gatway');  
    });  
    $(event.target).addClass('selected-gatway');  
  }

</script>