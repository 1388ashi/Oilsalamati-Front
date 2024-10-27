<script src="{{ asset('front/assets/js/plugins.js') }}"></script>
<script src="{{ asset('front/assets/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('front/assets/js/vendor/jquery.elevatezoom.js') }}"></script>
<script src="{{ asset('front/assets/js/main.js') }}"></script>
<script src="{{ asset('front/assets/js/vendor/photoswipe.min.js') }}"></script>


<script>
    $(".select2").select2();
    $(".multi-select2").select2({
        tags: true
    });
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
