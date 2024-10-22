<script>
        $(document).ready(function () {
            if (localStorage.getItem("deletedItems")) {
                swal({
                    title: 'موفق شد!',
                    text: 'آیتم هایی که قابل حذف بودند، با موفقیت حذف شدند!',
                    icon: 'success',
                    dangerMode: false
                })
                localStorage.removeItem("deletedItems", true);
            }
        });

        $(function () {
                if (sessionStorage.reloadAfterPageLoad == true) {
                    swal({
                        title: 'موفق شد!',
                        text: 'آیتم هایی که قابل حذف بودند، با موفقیت حذف شدند!',
                        icon: 'success',
                        dangerMode: false
                    })
                    sessionStorage.reloadAfterPageLoad = false;
                }
            }
        );
        $(document).ready(function () {
            $('#check_all').on('click', function (e) {
                if ($('#check_all').is(':checked', true)) {
                    $(".checkbox").prop('checked', true);
                } else {
                    $(".checkbox").prop('checked', false);
                }
            });

            $('.checkbox').on('click', function () {
                if ($('.checkbox:checked').length == $('.checkbox').length) {
                    $('#check_all').prop('checked', true);
                } else {
                    $('#check_all').prop('checked', false);
                }
            });
            $('.delete-all').on('click', function (e) {
                var idsArr = [];
                $(".checkbox:checked").each(function () {
                    idsArr.push($(this).attr('data-id'));
                });
                if (idsArr.length <= 0) {
                    swal({
                        title: 'انتخاب نکردید!',
                        text: 'لطفاً حداقل یک مورد را برای حذف انتخاب کنید',
                        icon: 'warning',
                        dangerMode: true
                    })
                } else {
                    swal({
                        title: 'آیا مطمئن هستید؟',
                        text: 'بعد از حذف این آیتم دیگر قابل بازیابی نخواهد بود!',
                        icon: 'warning',
                        buttons: ["انصراف", "حذف کن"],
                        dangerMode: true
                    })
                        .then((willDelete) => {
                            if (willDelete) {
                                var strIds = idsArr.join(",");
                                $.ajax({
                                    type: 'DELETE',
                                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                                    data: 'ids=' + strIds,
                                    success: function (data) {
                                        if (data['status'] == true) { //1
                                            $(".checkbox:checked").each(function () {
                                                $(this).parents("tr").remove();
                                            });
                                            localStorage.setItem("deletedItems", true);
                                            location.reload();
                                        } else {
                                            swal({
                                                title: 'مشکل!',
                                                text: 'مشکلی در اجرای عملیات رخ داده است. مجدداً تلاش کنید.',
                                                icon: 'warning',
                                                dangerMode: true
                                            })
                                        }
                                    },

                                    error: function (data) {
                                        localStorage.setItem("deletedItems", true);
                                        location.reload();
                                    }
                                });
                            }
                        });
                }
            });

            $('[data-toggle=confirmation]').confirmation({
                rootSelector: '[data-toggle=confirmation]',
                onConfirm: function (event, element) {
                    element.closest('form').submit();
                }
            });
        });
    </script>
