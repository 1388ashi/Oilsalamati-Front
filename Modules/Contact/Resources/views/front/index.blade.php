@extends('front.layouts.master')
@section('body_class') contact-page contact-style1-page @endsection
@section('content')
<div class="page-header mt-0 py-3">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                <div class="breadcrumbs">
                    <a href="/" title="Back to the home page">صفحه اصلی</a>
                    <span class="main-title fw-bold">
                        <i class="icon anm anm-angle-left-l"></i>
                        تماس با ما
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container contact-style1">
    <div class="contact-form-details section pt-0">
        <div class="row">
            <div class="col-12 col-sm-12 col-md-8 col-lg-8">
                <div class="formFeilds contact-form form-vertical mb-4 mb-md-0">
                <div class="section-header">
                    <h2>بیایید با هم تماس بگیریم!</h2>
                    <p>
                        {{ $settings[4]['value'] }}
                    </p>
                </div>

                <form action="{{route('contacts.store')}}" method="post" id="contact-form" class="contact-form">
                    <div class="form-row">
                    <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                        <input
                            type="text"
                            id="ContactFormName"
                            name="name"
                            class="form-control"
                            placeholder="نام"
                            value="{{old('name')}}"
                        />
                        <span class="error_msg" id="name_error"></span>
                        </div>
                    </div>
                    <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                        <input
                            type="email"
                            id="ContactFormEmail"
                            name="email"
                            class="form-control"
                            placeholder="ایمیل"
                        />
                        <span class="error_msg" id="email_error"></span>
                        </div>
                    </div>
                    </div>
                    <div class="form-row">
                    <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                        <input
                            class="form-control"
                            type="tel"
                            id="ContactFormPhone"
                            name="phone_number"
                            pattern="[0-9\-]*"
                            placeholder="شماره تلفن"
                            value="{{old('phone_number')}}"
                        />
                        </div>
                    </div>
                    <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                        <input
                            type="text"
                            id="ContactSubject"
                            name="subject"
                            class="form-control"
                            placeholder="عنوان"
                            value="{{old('subject')}}"
                        />
                        <span class="error_msg" id="subject_error"></span>
                        </div>
                    </div>
                    </div>
                    <div class="form-row">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="form-group">
                        <textarea
                            id="ContactFormMessage"
                            name="body"
                            class="form-control"
                            rows="5"
                            placeholder="پیام"
                            value="{{old('body')}}"
                        ></textarea>
                        <span class="error_msg" id="message_error"></span>
                        </div>
                    </div>
                    </div>
                    <div class="form-row">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="form-group mailsendbtn mb-0 w-100">
                        <input
                            class="btn btn-lg"
                            type="submit"
                            name="contactus"
                            value="ارسال پیام"
                        />
                        <div class="loading">
                            <img
                            class="img-fluid"
                            src="assets/images/icons/ajax-loader.gif"
                            alt="منتظر بمانید"
                            width="16"
                            height="16"
                            />
                        </div>
                        </div>
                    </div>
                    </div>
                </form>
                <div class="response-msg"></div>
                </div>
                <!-- پایان فرم تماس -->
            </div>
            <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                <!-- اطلاعات تماس -->
                <div class="contact-details bg-block">
                    <h3 class="mb-3 fs-5">اطلاعات ذخیره</h3>
                    <ul class="list-unstyled">
                        <li class="mb-2 address">
                        <strong class="d-block mb-2">آدرس :</strong>
                        <p>
                            <i class="icon anm anm-map-marker-al ms-2 d-none"></i>
                            {{ $settings[7]['value'] }}
                        </p>
                        </li>
                        <li class="mb-2 phone">
                        <strong>تلفن :</strong
                        ><i class="icon anm anm-phone ms-2 d-none"></i>
                        <a dir="ltr">{{ $settings[2]['value'] }}</a>
                        </li>
                        <li class="mb-0 email">
                        <strong dir="ltr">:ایمیل </strong
                        ><i class="icon anm anm-envelope-l ms-2 d-none"></i>
                        <a href="mailto:{{ $settings[6]['value'] }}">{{ $settings[6]['value'] }}</a>
                        </li>
                    </ul>
                <hr />
                    <div class="follow-us">
                        <label class="d-block mb-3"
                        ><strong>در ارتباط بمانید</strong></label
                        >
                        <ul class="list-inline social-icons">
                        <li class="list-inline-item">
                            <a
                            href="#"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="فیس بوک"
                            ><i class="icon anm anm-facebook-f"></i
                            ></a>
                        </li>
                        <li class="list-inline-item">
                            <a
                            href="#"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="توییتر"
                            ><i class="icon anm anm-twitter"></i
                            ></a>
                        </li>
                        <li class="list-inline-item">
                            <a
                            href="#"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="پینترست"
                            ><i class="icon anm anm-pinterest-p"></i
                            ></a>
                        </li>
                        <li class="list-inline-item">
                            <a
                            href="#"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="لینکدین"
                            ><i class="icon anm anm-linkedin-in"></i
                            ></a>
                        </li>
                        <li class="list-inline-item">
                            <a
                            href="#"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="گوگل پلاس"
                            ><i class="icon anm anm-google-plus-g"></i
                            ></a>
                        </li>
                        <li class="list-inline-item">
                            <a
                            href="#"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="واتس اپ"
                            ><i class="icon anm anm-whatsapp"></i
                            ></a>
                        </li>
                        </ul>
                    </div>
                </div>
                <!-- پایان اطلاعات تماس -->
            </div>
        </div>
    </div>
    <div class="contact-maps section p-0">
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                <div class="map-section ratio ratio-16x9">
                <iframe
                    class="rounded-5"
                    src="https://www.google.com/maps/embed?pb="
                    allowfullscreen=""
                    height="650"
                ></iframe>
                <div class="map-section-overlay-wrapper">
                    <div class="map-section-overlay rounded-0">
                    <h3>فروشگاه ما</h3>
                    <div class="content mb-3">
                        <p class="mb-2">
                        123، نام شرکت شما،<br />تورنتو، کانادا
                        </p>
                        <p>
                        دوشنبه - جمعه، 10 صبح تا 9 بعد از ظهر<br />شنبه، 11
                        صبح تا 9 بعد از ظهر<br />یکشنبه، 11 صبح تا 5 بعد از
                        ظهر
                        </p>
                    </div>
                    <p>
                        <a
                        href="https://www.google.com/maps?daddr=80+Spadina+Ave,+Toronto"
                        class="btn btn-secondary btn-sm"
                        >دریافت مسیرها</a
                        >
                    </p>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
<!-- پایان نقشه تماس -->
</div>
@endsection
