<script>

	$('#{{ $productInputId }}').select2({placeholder: 'انتخاب محصول'});
	$('#{{ $varietyInputId }}').select2({placeholder: 'ابتدا محصول را انتخاب کنید سپس تنوع'});

 	$('#{{ $productInputId }}').change(() => {
    $.ajax({
      url: '{{ route('admin.stores.load-varieties') }}',
      type: 'POST',
      data: {product_id: $('#{{ $productInputId }}').val()},
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      success: function(response) {
				console.log(response.varieties);
        if (Array.isArray(response.varieties)) {  
					let options = '';  
					response.varieties.forEach((variety) => {  
						let id = variety.id;  
						let balance = variety.store_balance ?? 0;  
						let attr = variety.color ? variety.color.name : (variety.attributes[0]?.pivot?.value ?? null);  
						let optionTitle = `شناسه: ${id}` + (attr ? ` | ${attr}` : '') + ` | موجودی: ${balance}`;  
						options += `<option value="${variety.id}">${optionTitle}</option>`;  
					});  
					$('#{{ $varietyInputId }}').html(options).trigger('change');  
				} else {  
					console.error('Expected varieties array, but got:', response.varieties);  
				}  
      }
    });
  });
</script>
