
(function ($) {
    // Start of use strict
    'use strict';

    /* Contact Form Submission */
    $(document).on('click', '.mailsendbtn .btn', function () {
        var frm = $(this).parents('form');

        var name = $('input[name="name"]').val();
        var phone = $('input[name="phone"]').val();
        var subject = $('input[name="subject"]').val();
        var message = $('textarea[name="message"]').val();
        var mail = $('input[name="email"]').val();
        var emailreg = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;

        var valid = true;

        if (name.trim() == "") {
            $('#name_error').html("لطفا نام را وارد کنید.");
            valid = false;
        } else {
            $('#name_error').html("");
        }

        if (mail.trim() == "") {
            $('#email_error').html("لطفا ایمیل را وارد کنید");
            valid = false;
        } else if (!emailreg.test(mail)) {
            $('#email_error').html("لطفا ایمیل معتبر وارد نمایید.");
            valid = false;
        } else {
            $('#email_error').html("");
        }

        if (subject.trim() == "") {
            $('#subject_error').html("لطفا موضوع را وارد کنید");
            valid = false;
        } else {
            $('#subject_error').html("");
        }

        if (message.trim() == "") {
            $('#message_error').html("لطفا پیام را وارد کنید");
            valid = false;
        } else {
            $('#message_error').html("");
        }

        if (valid) {
            $.ajax({
                type: "POST",
                url: "",
                data: frm.serialize(),
                // beforeSend: function () {
                //     $(".loading").show();
                // },

                success: function (response) {
                    // console.log($(this).serialize());
                    // console.log(JSON.parse(response));
                    // $(".loading").hide();
                    // var response = JSON.parse(response);
                    // if (response.success) {
                    //     $('.response-msg').html(response.success);
                    //     $('#contact-form')[0].reset();
                    // }
                    // if (response.error) {
                    //     $('.response-msg').html(response.error);
                    // }
                }
            });
        }
        return false;
    });

})(jQuery);

