@include('core::includes.date-input-script', [
    'textInputId' => 'start_date_show',
    'dateInputId' => 'start_date_hide',
])

@include('core::includes.date-input-script', [
    'textInputId' => 'end_date_show',
    'dateInputId' => 'end_date_hide',
])
<script>
    $('.search-customer-ajax').select2({
        ajax: {
            url: '{{ route('admin.customers.search') }}',
            dataType: 'json',
            processResults: (response) => {
                let customers = response.data.customers || [];

                return {
                    results: customers.map(customer => ({
                        id: customer.id,
                        mobile: customer.mobile,
                        name: customer.full_name || ''
                    })),
                };
            },
            cache: true,
        },
        placeholder: 'انتخاب مشتری',
        templateResult: formatRepo,
        minimumInputLength: 1,
        templateSelection: formatRepoSelection
    });

    function formatRepo(repo) {
        if (repo.loading) {
            return "در حال بارگذاری...";
        }

        var $container = $(
            "<div class='select2-result-repository clearfix'>" +
            "<div class='select2-result-repository__meta'>" +
            "<div class='select2-result-repository__title'></div>" +
            "</div>" +
            "</div>"
        );

        let text = `شناسه: ${repo.id} | موبایل: ${repo.mobile}`;
        if (repo.name) {
            text +=  ` | نام: ${repo.name}`;
        }
        $container.find(".select2-result-repository__title").text(text);

        return $container;
    }

    function formatRepoSelection(repo) {
        let text = `شناسه: ${repo.id} | موبایل: ${repo.mobile}`;
        if (repo.name) {
            text += ` | نام: ${repo.name}`;
        }
        return repo.id ? text : repo.text;
    }

    $('#status').select2({
        placeholder: 'انتخاب وضعیت',
        allowClear: true
    })
</script>
