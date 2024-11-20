<link href="{{ asset('front/assets/images/favicon.png') }}" rel="shortcut icon" />
<link href="{{ asset('front/assets/css/plugins.css') }}" rel="stylesheet" />
<link href="{{ asset('front/assets/css/vendor/photoswipe.min.css') }}" rel="stylesheet" />
<link href="{{ asset('front/assets/css/style-min.css') }}" rel="stylesheet" />
<link href="{{ asset('front/assets/css/responsive.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/css-rtl/icons.css')}}" rel="stylesheet" />
<link href="{{ asset('assets/font/font.css')}}" rel="stylesheet"/>
<link href="{{ asset('front/assets/css/custom.css') }}" rel="stylesheet" />

<style>
  @page {
    margin-block: 30px;
  }
  #growls-default {
      right: 80%;
    }
  h1, h2, h3, h4, h5, h6,input, textarea {
    font-family: Vazir !important;
  }
  .page-header {
    margin-top: 10px;
    margin-bottom: 10px;
  }
  .app-header {
    padding-top: 10px;
    padding-bottom: 10px;
  }

</style>

<style>

  #loader-div {
    width: 100%;
    height: 100%;
    background: #f3f3f3;
    box-sizing: border-box;
    justify-content: center;
    align-items: center;
    position: absolute;
    display: none;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1000;
  }

  #loader {  
    border: 10px solid #f3f3f3;  
    border-top: 10px solid #3498db;  
    border-radius: 50%;  
    width: 50px;  
    height: 50px;  
    animation: spin 1s linear infinite;  
  }  

  @keyframes spin {  
    0% { transform: rotate(0deg); }  
    100% { transform: rotate(360deg); }  
  }
</style>
