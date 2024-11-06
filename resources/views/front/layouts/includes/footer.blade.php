<div class="footer footer-6">
  <div class="footer-top clearfix">
    <div class="container container-1330">
      <div class="row">
        <div class="col-12 col-sm-12 col-md-12 col-lg-3 mb-4 mb-lg-0">
          <div class="ftr-logoText mb-3">
            <img
              class="logoImg"
              src="{{asset('front/assets/images/logo/logo.9208f443.svg')}}"
              alt="قالب Html چند منظوره Hema"
              title="قالب Html چند منظوره Hema"
              width="149"
              height="39"
            />
            <p class="mt-3 pt-1">
              قالب خلاق، انعطاف پذیر، بی نهایت و با کارایی بالا برای درخشش
              کسب و کار شما!
            </p>
          </div>
          <div class="phone-info d-flex align-items-center">
            <i class="phone-icon icon anm anm-phone-l"></i>
            <div class="phone-text d-flex flex-column">
              <label class="m-0"
                >سوالی دارید؟ 24 ساعته با ما تماس بگیرید</label
              >
              <a href="tel:+801234564790" class="w-100"
                ><span dir="ltr">(+80) 123 456 4790</span></a
              >
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-12 col-md-12 col-lg-9">
          <div class="row">
            @foreach ($menu['footer'] as $menu)
            <div class="col-12 col-sm-12 col-md-4 col-lg-3 footer-links ps-lg-5">
              @if ($menu['children'])
                <h4 class="h4">{{ $menu['title'] }}</h4>
                <ul>
                  @foreach ($menu['children'] as $item)
                    <li><a href="{{ $item['link'] }}">{{ $item['title'] }}</a></li>
                  @endforeach
                </ul>
              @else
                <a class="h4" href="{{ $menu['link'] }}">{{ $menu['title'] }}</a>
              @endif
            </div>
            @endforeach
            <div
              class="col-12 col-sm-12 col-md-12 col-lg-4 mt-1 mt-md-4 mt-lg-0"
            >
              <div class="footer-newsletter icon-arrow">
                <form action="#" method="post">
                  <label class="h4 mb-2"
                    >در خبرنامه ما ثبت نام کنید</label
                  >
                  <p>
                    مطمئن شوید که هرگز اخبار جالب و تبلیغات انحصاری ما را
                    از دست ندهید.
                  </p>
                  <div class="input-group">
                    <input
                      type="email"
                      class="form-control input-group-field newsletter-input rounded-5"
                      name="email"
                      value=""
                      placeholder="آدرس ایمیل خود را وارد کنید..."
                      required
                    />
                    <button
                      type="submit"
                      class="input-group-btn text-link newsletter-submit"
                      name="commit"
                    >
                      <i class="icon anm anm-arw-left"></i>
                    </button>
                  </div>
                </form>
                <ul
                  class="list-inline social-icons d-inline-flex mt-3 pt-1"
                >
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
                      title="اینستاگرام"
                      ><i class="icon anm anm-instagram"></i
                    ></a>
                  </li>
                  <li class="list-inline-item">
                    <a
                      href="#"
                      data-bs-toggle="tooltip"
                      data-bs-placement="top"
                      title="یوتیوب"
                      ><i class="icon anm anm-youtube"></i
                    ></a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="footer-bottom clearfix">
    <div class="container container-1330">
      <div
        class="d-flex-center flex-column justify-content-md-between flex-md-row-reverse py-lg-1"
      >
        <ul class="payment-icons d-flex-center mb-2 mb-md-0">
          <li><i class="icon anm anm-cc-visa"></i></li>
          <li><i class="icon anm anm-cc-mastercard"></i></li>
          <li><i class="icon anm anm-cc-amex"></i></li>
          <li><i class="icon anm anm-cc-paypal"></i></li>
          <li><i class="icon anm anm-cc-discover"></i></li>
          <li><i class="icon anm anm-cc-stripe"></i></li>
          <li><i class="icon anm anm-cc-jcb"></i></li>
        </ul>
        <div class="copytext">&copy; 2023 هما تمامی حقوق محفوظ است.</div>
      </div>
    </div>
  </div>
</div>

<div id="site-scroll"><i class="icon anm anm-arw-up"></i></div>
