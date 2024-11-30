<script src="{{ asset('front/assets/js/plugins.js') }}"></script>
<script src="{{ asset('front/assets/js/main.js') }}"></script>
<script src="{{ asset('assets/plugins/jquery/jquey.min.js') }}"></script>
<script src="{{ asset('front/assets/custom/sweetalert.min.js') }}"></script>
<script src="{{ asset('assets/plugins/notify/js/jquery.growl.js') }}"></script>
<script src="{{ asset('front/assets/custom/custom.js') }}"></script>


<script>
    let typingTimer;

    $('#SearchProductInput').on('input', function() {
        clearTimeout(typingTimer);
        const searchInput = $.trim($(this).val());

        typingTimer = setTimeout(function() {
            if (searchInput !== null && searchInput != '' && searchInput != ' ') {
                searchProducts(searchInput);
            }
        }, 1600);
    });

    function searchProducts(value) {
        $.ajax({
            url: '{{ route('products.search') }}',
            type: 'GET',
            data: {
                q: value
            },
            success: function(response) {

                if (response.data.length == 0 || response.data.products.length == 0) {
                    
                    $("#ProductSearchBox").html(
                        '<li class="item vala w-100 text-center text-muted">شما هیچ موردی در جستجوی خود ندارید.</li>'
                    );
                    return;
                }

                const products = response.data.products;
                const ProductSearchItem = $('#ProductSearchItem');
                let items;

                $('#ProductSearchBox').empty();

                products.forEach(product => {

                    let item = ProductSearchItem.clone();

                    let img = item.find('.item-image');
                    let title = item.find('.item-title');
                    let oldPrice = item.find('.old-price');
                    let price = item.find('.price');
                    let link = item.find('a');

                    item.removeAttr('id');
                    item.removeClass('d-none');
                    link.attr('href', '{{ route('products.show', '') }}' + '/' + product.id);

                    img.attr({
                        dataSrc: product.images_showcase.main_image.url,
                        src: product.images_showcase.main_image.url,
                        alt: product.title,
                        title: product.title
                    });

                    let limitedTitle = product.title.length > 28 ? product.title.slice(0, 28) + '...' : product.title;
                    title.text(product.title);
                    price.text(product.final_price.base_amount.toLocaleString() + ' ' + 'تومان');

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
