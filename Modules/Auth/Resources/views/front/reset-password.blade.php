<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content="description">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تغییر رمز عبور</title>
    <link rel="shortcut icon" href="{{asset('front/assets/images/favicon.png')}}" />
    <link rel="stylesheet" href="{{asset('front/assets/css/plugins.css')}}">
    <link rel="stylesheet" href="{{asset('front/assets/css/style-min.css')}}">
    <link rel="stylesheet" href="{{asset('front/assets/css/responsive.css')}}">
</head>
<style>
    body {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }
    .container {
        height: 80%;
        width: 100%;
        max-width: 400px;
        padding: 40px;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .logo {
        text-align: center;
        margin-bottom: 20px;
    }
    .btn-register {
        width: 100%;
        border-radius: 10px;
    }
    .input-register {
        width: 100%;
        border-radius: 10px;
    }
    .warning-text {
        font-size: 10px;
    }
    .text-strong {
        font-size: 13px;
    }
</style>
<body class="account-page register-page">
    <div class="page-wrapper d-flex-justify-center">
        <div class="container">
            <div class="row my-3 d-flex" style="flex-direction: column; align-items: center;">
                <div class="logo d-flex-justify-center mt-1" style="align-items: center;">
                    <img
                        src="{{asset('front/assets/images/logo/logo.9208f443.svg')}}"
                        alt="قالب چند منظوره هما"
                        title="قالب چند منظوره هما"
                        width="149"
                        height="39"
                    />
                </div>
                <x-alert-danger></x-alert-danger>
                <x-alert-success></x-alert-success>
                <form action="{{ route('resetPassword') }}" method="POST">
                    @csrf
                    <input type="hidden" name="sdvssdfsdv" value="brthtyjuj7s">
                    <input type="hidden" name="mobile" value="{{$mobile}}">
                    <div class="form-group mb-3">
                        <strong class="mb-2" class="text-strong">رمز عبور خود را بازیابی کنید</strong>
                        <input type="text" name="password" class="form-control input-register" placeholder="مثال: 123456" required value="{{old('password')}}">
                    </div>
                    <div class="form-group mb-2">
                        <strong class="mb-2" class="text-strong">تکرار رمز عبور</strong>
                        <input type="text" name="password_confirmation" class="form-control input-register" placeholder="رمز عبور را تکرار کنید" required value="{{old('password_confirmation')}}">
                        <p class="text-danger warning-text mt-1">این قسمت را خالی نگذارید</p>
                    </div>
                    <button type="submit" class="btn btn-secondary btn-register mb-2">ورود</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
