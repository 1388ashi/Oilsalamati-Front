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