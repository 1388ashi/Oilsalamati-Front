<script>
    function removeProduct(event){
        console.log(event.target);

        Swal.fire ({  
            text: 'آیا تمایل دارید محصول را از لیست علاقه مندی ها حذف کنید',
            icon: "warning",  
            confirmButtonText: 'حذف کن',  
            showDenyButton: true,  
            denyButtonText: 'انصراف',  
            dangerMode: true,  
            }).then((result) => {  
            if (result.isConfirmed) {  
                const btn = $(event.target); 
                $.ajax({  
                    url: btn.data("url"),  
                    type: 'DELETE',  
                    headers: {  
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',  
                    },  
                    success: function(response) {
                        btn.closest("tr").remove();
                        Swal.fire({  
                            icon: "success",  
                            text: response.message  
                        });  
                    },  
                    error: function(error) {  
                        console.log(error);  
                        Swal.fire({  
                            icon: "error",  
                            text: error.responseJSON.message  
                        });  
                    }  
                }); 
            } 
        }); 
    }
</script>