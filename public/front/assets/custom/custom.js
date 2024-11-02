function confirmDelete(formId) {  
  Swal.fire ({  
    title: "آیا مطمئن هستید؟",  
    text: "بعد از حذف این آیتم دیگر قابل بازیابی نخواهد بود!",  
    icon: "warning",  
    confirmButtonText: 'حذف کن',  
    showDenyButton: true,  
    denyButtonText: 'انصراف',  
  }).then((result) => {  
    if (result.isConfirmed) {  
      document.getElementById(formId).submit();  
      Swal.fire({  
        icon: "success",  
        text: "آیتم با موفقیت حذف شد."  
      });  
    } else if (result.isDenied) {  
      Swal.fire({  
        icon: "info",  
        text: "عملیات حذف نادیده گرفته شد."  
      });  
    }  
  });  
}  

function comma() {
  $("input.comma").on("keyup", function (event) {
    if (event.which >= 37 && event.which <= 40) return;
    $(this).val(function (index, value) {
      return value
        .replace(/\D/g, "")
        .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    });
  });
}

$(document).ready(function () {
  comma();
});

function popup(title, type, message) {
  Swal.fire ({  
    title: title,
    text: message,
    icon: type,  
    confirmButtonText: 'بستن',  
  });
}

function showErrorMessages(error) {
  let messages = '';  

  if (error.responseJSON.errors) {  
    for (const key in error.responseJSON.errors) {  
      if (error.responseJSON.errors.hasOwnProperty(key)) {  
        error.responseJSON.errors[key].forEach(message => {  
          messages += ' ' + message;  
        });  
      }  
    }  
  } else {  
    messages = error.responseJSON.message || 'An unknown error occurred.';  
  }  

  popup('خطا در برداشت', 'error', messages); 
}
