@extends('front.layouts.master')

@section('title')
    <title>اطلاعات حساب کاربری</title>
@endsection

@section('styles')
    @include('customer::front.includes.styles.info')
    @include('customer::front.includes.styles.wallet')
@endsection

@section('body_class')
    account-page my-account-page
@endsection

@section('content')
    <div class="page-header mt-0 py-2">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="breadcrumbs">
                        <a href="/" title="Back to the home page">صفحه اصلی</a>
                        <span class="main-title fw-bold">
                            <i class="icon anm anm-angle-left-l"></i>
                            حساب من
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-alert-danger />

    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-3 mb-4 mb-lg-0">
                <div class="dashboard-sidebar bg-block">
                    <div class="profile-top text-center mb-4 px-3">
                        <div class="profile-image mb-3">
                            @php
                                $userImageUrl = $customer->image?->url ?? 'front/assets/images/users/no-user.jpg';
                            @endphp
                            <img class="rounded-circle user-image blur-up lazyload" src="{{ asset($userImageUrl) }}"
                                alt="user" width="130" />
                            <form id="imageUploadForm" enctype="multipart/form-data"
                                action="{{ route('customer.profile.uploadImage') }}" method="POST">
                                <input type="file" style="widows:0; height:0" id="ImageBrowse"
                                    onchange="$('#imageUploadForm').submit()" hidden="hidden" name="image"
                                    size="30" />
                            </form>
                            <div class="overlay" onclick="$('#ImageBrowse').click()">
                                <i class="icon anm anm-pencil-square-o text-light"></i>
                            </div>
                        </div>
                        <div class="profile-detail">

                            <h3 class="mb-1 full-name">{{ $customer->full_name }}</h3>

                            <div class="d-flex align-items-center justify-content-center gap-2">

                                <p class="text-muted m-0">
                                    موجودی کیف پول :
                                    <b class="text-dark wallet-balance"
                                        data-balance="{{ $customer->balance }}">{{ number_format($customer->balance) }}
                                        تومان</b>
                                </p>

                                <button type="button" class="d-flex justify-content-center text-center"
                                    style="border-radius: 50%; padding-inline: 10px; padding-top: 4px; background-color: #2F415d;"
                                    data-bs-toggle="modal" data-bs-target="#DepositWalletModal">
                                    <span class="text-center text-light">+</span>
                                </button>

                            </div>
                        </div>
                    </div>
                    @include('customer::front.includes.tabs.nav-tabs')
                </div>
            </div>
            <div class="col-12 col-sm-12 col-md-12 col-lg-9">
                <div class="dashboard-content tab-content h-100" id="top-tabContent">
                    @include('customer::front.includes.tabs.info')
                    @include('customer::front.includes.tabs.address')
                    @include('customer::front.includes.tabs.orders')
                    @include('customer::front.includes.tabs.favorites')
                    @include('customer::front.includes.tabs.wallet')
                </div>
            </div>
        </div>
    </div>

    @include('customer::front.includes.examples.address-box')
@endsection

@section('scripts')
    @include('customer::front.includes.scripts.addresses')
    @include('customer::front.includes.scripts.wallet')
    @include('customer::front.includes.scripts.info')
    @include('customer::front.includes.scripts.favorites')

    <script>
        const fileInput = $('#ImageBrowse').get(0);

        async function getBase64(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = () => resolve(reader.result.split(',')[1]);
                reader.onerror = error => reject(error);
            });
        }

        function storeUserImage(e) {
            e.preventDefault();

            if (fileInput.files && fileInput.files.length > 0) {
                const file = fileInput.files[0];
                getBase64(file).then(base64 => {
                    console.log(file);
                    return;
                    $.ajax({
                        type: 'PUT',
                        url: $(e.target).attr('action'),
                        data: {
                            image: base64,
                        },
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        success: function(data) {
                            console.log("success");
                            console.log(data);
                        },
                        error: function(data) {
                            console.log("error");
                            console.log(data);
                        }
                    });
                }).catch(error => {
                    console.error("Error converting to base64:", error);
                });
            } else {
                console.log('No file selected.');
            }
        }

        $('#imageUploadForm').on('submit', storeUserImage);
    </script>
@endsection
