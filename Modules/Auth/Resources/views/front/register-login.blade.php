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
    .login-container {  
        height: 430px;
        width: 350px;  
        max-width: 400px;  
        padding: 30px;  
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
        direction: ltr;
        text-align: left;  
        width: 100%;  
        border-radius: 10px;  
    }  
    .warning-text {  
        font-size: 10px;  
    }  
    .text-downpage {  
        font-size: 12px;  
    }  
</style>   
<body class="account-page register-page">  
    <div class="page-wrapper d-flex-justify-center">  
            <div class="login-container">  
                <div class="row my-3" style="display: flex; flex-direction: column; align-items: center;">  
                    <div class="logo d-flex-justify-center"style="flex-direction: column; align-items: center;">  
                        <img  
                            src="{{asset('front/assets/images/logo/logo.9208f443.svg')}}"
                            alt="قالب چند منظوره هما"  
                            title="قالب چند منظوره هما"  
                            width="149"  
                            height="39"  
                        />  
                    </div>  
                    <h5>ورود | ثبت‌نام</h5>  
                    <p style="margin-bottom: 0.5rem">سلام!</p>  
                    <p>لطفا شماره موبایل خود را وارد کنید</p>  
                    <x-alert-danger></x-alert-danger>
                    <x-alert-success></x-alert-success>
                    <form action="{{ route('registerLogin') }}" method="POST">
                        @csrf  
                        {{-- <input type="hidden" name="sdvssdfsdv" value="brthtyjuj7s"> --}}
                        <div class="form-group mb-3">  
                            <input type="text" name="mobile" class="form-control input-register" value="09" placeholder="شماره موبایل" required oninput="validateInput(this)" style="direction: ltr;" value="{{old('mobile',$mobile)}}" required>  
                        </div>  
                        <button type="submit" class="btn btn-secondary btn-register">ورود</button>  
                    </form>  
                    <p class="text-center mt-3 text-downpage">ورود شما به معنای پذیرش شرایط و قوانین حریم‌ خصوصی است</p>  
                </div>  
            </div>  
    </div>  

    <script>  
        function validateInput(input) {  
            input.value = input.value.replace(/[^0-9]/g, '');  
        
            if (input.value.length > 11) {  
                input.value = input.value.slice(0, 11);
            }  
            if (input.value === '') {  
                input.value = '09';  
            }  
            if (input.value.length < 2 && input.value !== '09') {  
                input.value = '09';  
            }  
        }  
        </script>  
</body>
</html>