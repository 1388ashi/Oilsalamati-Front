<script src="{{ asset('front/assets/js/plugins.js') }}"></script>
<script src="{{ asset('front/assets/js/main.js') }}"></script>
<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('front/assets/custom/custom.js') }}"></script>
<script src="{{ asset('front/assets/custom/sweetalert.min.js') }}"></script>
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script> --}}


<script>
    let typingTimer;

    $('#SearchProductInput').on('input', function() {
        clearTimeout(typingTimer);
        const searchInput = $(this).val();

        typingTimer = setTimeout(function() {
            searchProducts(searchInput);
        }, 1000);
    });

    function searchProducts(value) {
        $.ajax({
            url: '{{ route('products.search') }}',
            type: 'GET',
            data: {
                q: value
            },
            success: function(response) {

                if (response.data.products.length == 0) {
                    $("#ProductSearchBox").append(
                        '<li class="item vala w-100 text-center text-muted">شما هیچ موردی در جستجوی خود ندارید.</li>'
                        );
                    return;
                }

                const products = response.data.products;
                const ProductSearchItem = $('#ProductSearchItem');

                $('#ProductSearchBox').empty();

                products.forEach(product => {

                    let item = ProductSearchItem.clone();

                    let img = item.find('#ProductSearchImage');
                    let title = item.find('#ProductSearchTitle');
                    let oldPrice = item.find('#ProductSearchOldPrice');
                    let price = item.find('#ProductSearchPrice');
                    let link = item.find('a')

                    item.removeAttr('id');
                    item.removeClass('d-none');
                    link.attr('href', '{{ route('products.show', '') }}' + '/' + product.id);

                    img.attr({
                        dataSrc: product.images_showcase.main_image.url,
                        src: product.images_showcase.main_image.url,
                        alt: product.title,
                        title: product.title
                    });

                    let limitedTitle = product.title.length > 28 ? product.title.slice(0, 28) +
                        '...' : product.title;
                    title.text(product.title);
                    price.text(product.final_price.base_amount.toLocaleString() + ' ' + 'تومان');

                    img.removeAttr('id');
                    title.removeAttr('id');
                    price.removeAttr('id');

                    if (product.final_price.discount > 0) {
                        oldPrice.text(product.final_price.amount.toLocaleString() + ' ' + 'تومان');
                        oldPrice.removeAttr('id');
                    } else {
                        oldPrice.remove();
                    }

                    $("#ProductSearchBox").append(item);

                });

            }
        });
    }
</script>

@if (session()->has('success'))
    <script>
        $(document).ready(function() {
            $.growl.notice({
                title: "موفق شد!",
                message: "{{ session('success') }}"
            });
        });
    </script>
@elseif(session()->has('error'))
    <script>
        $(document).ready(function() {
            $.growl.error({
                title: "خطایی رخ داده!",
                message: "{{ session('error') }}"
            });
        });
    </script>
@elseif(session()->has('warning'))
    <script>
        $(document).ready(function() {
            $.growl.warning({
                title: "هشدار!",
                message: "{{ session('warning') }}"
            });
        });
    </script>
@elseif(session()->has('info'))
    <script>
        $(document).ready(function() {
            $.growl.warning({
                title: "هشدار!",
                message: "{{ session('warning') }}"
            });
        });
    </script>
@endif
