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
<style>
    body {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0; 
    }  
    .container {
        height: 440px;
        width: 350px;
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
        margin-top: 20px;
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
    .text {  
        font-size: 13px;  
    }  
    .edit-mobile:hover {  
        color: #e96f84;  
    }  
</style>   
<body class="account-page register-page">  
    <div class="page-wrapper d-flex-justify-center">  
            <div class="container">  
                <div class="row my-3 d-flex" style="flex-direction: column; align-items: center;">  
                    <div class="logo d-flex-justify-center mt-1" style="align-items: center;">  
                        <img  
                            src="{{asset('front/assets/images/logo.png')}}"  
                            alt="قالب چند منظوره هما"  
                            title="قالب چند منظوره هما"  
                            width="149"  
                            height="39"  
                        />  
                    </div>  
                    <x-alert-danger></x-alert-danger>
                    <x-alert-success></x-alert-success>
                    <form action="{{ route('register') }}" method="POST">
                        @csrf
                        <input type="hidden" name="password" value="brthtyjuj7s">
                        <input type="hidden" name="type" value="register">
                        <input type="hidden" name="mobile" value="{{$mobile}}">
                        <div class="form-group mb-">
                            <strong for="">کد تایید را وارد کنید</strong>
                            <p class="text mt-1">لطفا کد ارسال شده به شماره {{$mobile}} را وارد کنید.</p>
                            <input type="text" name="sms_token" class="form-control input-register" placeholder="مثال: 1234"  required oninput="validateInput(this)" style="direction: ltr;" value="{{old('sms_token')}}">
                            <p class="text-danger warning-text mt-1">این قسمت را خالی نگذارید</p>
                        </div>
                        <div id="timer" class="text-center">02:00</div>
                        <a href="" onclick="document.getElementById('postForm').submit();" id="messageBox" class="btn btn-secondary btn-register mb-2" style="display: none">ارسال مجدد کد</a>
                        <button type="submit" class="btn btn-secondary btn-register mb-2">ورود</button>
                        <div class="d-flex-justify-center" style="align-items: center">
                            <a href="{{ route('pageRegisterLogin',$mobile) }}" class="edit-mobile">ویرایش شماره</a>
                        </div>
                    </form>
                </div>
            </div>
    </div>
    <form id="postForm" action="{{ route('registerLogin', $mobile) }}" method="POST" style="display: none;">
        @csrf
    </form>
    <script>
        let seconds = 120;

        const timerElement = document.getElementById("timer");
        const messageBox = document.getElementById("messageBox");

        const countdown = setInterval(() => {
            const minutes = Math.floor(seconds / 60);
            const secs = seconds % 60;

            timerElement.textContent = `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;

            if (seconds > 0) {
                seconds--;
            } else {
                clearInterval(countdown);
                messageBox.style.display = 'flex';
            }
        }, 1000);
        function validateInput(input) {
            if (input.value.length > 4) {
                input.value = input.value.slice(0, 4);
            }
        }
        function validatePassword(input) {
            if (input.value.length > 4) {
                input.value = input.value.slice(0, 4);
            }
        }
        </script>
</body>
</html>
