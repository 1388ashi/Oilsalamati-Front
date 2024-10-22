$('.search-customer-ajax').select2({
  ajax: {
    url: '{{ route("admin.customers.search") }}',
    dataType: 'json',
    processResults: (data) => {
      console.log(data.customers);
    }
  }
});
