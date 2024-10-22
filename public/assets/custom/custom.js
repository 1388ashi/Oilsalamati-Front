function confirmDelete(formId) {
  swal({
    title: "آیا مطمئن هستید؟",
    text: "بعد از حذف این آیتم دیگر قابل بازیابی نخواهد بود!",
    icon: "warning",
    buttons: ["انصراف", "حذف کن"],
    dangerMode: true,
  }).then((willDelete) => {
    if (willDelete) {
      document.getElementById(formId).submit();
      swal("آیتم با موفقیت حذف شد.", {
        icon: "success",
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
