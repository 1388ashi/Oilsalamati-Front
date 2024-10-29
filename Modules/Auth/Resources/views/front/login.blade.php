<!DOCTYPE html>
<html class="no-js" lang="en">
    
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="description" content="description">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ورود | ثبت نام</title>
  <link rel="shortcut icon" href="{{asset('front/assets/images/favicon.png')}}" />
  <link rel="stylesheet" href="{{asset('front/assets/css/plugins.css')}}">
  <link rel="stylesheet" href="{{asset('front/assets/css/style-min.css')}}">
  <link rel="stylesheet" href="{{asset('front/assets/css/responsive.css')}}">
</head>

  <body class="account-page register-page">
    <div class="page-wrapper">
      <div id="page-content " class="mb-0">
        <div class="container">   
          <div class="login-register">
            <div class="row d-flex-justify-center" style="height: 100vh;">
              <div class="col-12 col-sm-12 col-md-8 col-lg-4">
                <div class="inner h-100">
                  <form method="POST" action="{{ route('customer.login') }}" class="customer-form">
                    @csrf
                    <h2 class="text-center fs-4 mb-4">ورود و ثبت نام</h2>
                    <p class="text-center">لطفا برای ادامه شماره همراه خود را وارد نمایید.</p>
                    <div class="form-row">
                      <div class="form-group col-12">
                        <label for="mobile" class="d-none">شماره همراه<span class="required">*</span></label>
                        <input type="text" name="mobile" placeholder="شماره همراه" id="mobile" value="{{ old('mobile') }}" required />
                      </div>
                      @if (session()->has('status') && session('status') == 'danger')
                      <div class="form-group col-12">
                        <div class="alert alert-danger">
                          <ul>
                            <li>{{ session('message') }}</li>
                          </ul>
                        </div>
                      </div>
                      @endif
                      <div class="form-group col-12 mb-0">
                        <input type="submit" class="btn btn-primary btn-lg w-100" value="ادامه" />
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <script src="{{asset('front/assets/js/plugins.js')}}"></script>
      <script src="{{asset('front/assets/js/main.js')}}"></script>

    </div>
  </body>

</html>