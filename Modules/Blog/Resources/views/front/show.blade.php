@extends('front.layouts.master')
@section('body_class') blog-page blog-details-page @endsection
@section('content')
<div class="page-header mt-0 py-3">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                <div class="breadcrumbs">
                    <a href="/" title="Back to the home page">صفحه اصلی</a>
                    <a href="{{route('products.index')}}" title="Back to the home page">
                        <i class="icon anm anm-angle-left-l"></i>
                        وبلاگ</a>
                    <span class="main-title fw-bold">
                        <i class="icon anm anm-angle-left-l"></i>
                        جزئیات وبلاگ
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-12 col-sm-12 col-md-12 col-lg-3 blog-sidebar sidebar sidebar-bg">
            <div class="sidebar-tags sidebar-sticky clearfix">
                <div class="sidebar-widget clearfix categories">
                    <div class="widget-title"><h2>دسته</h2></div>
                    <div class="widget-content">
                        <ul class="sidebar-categories scrollspy clearfix">
                            @foreach ($post['category'] as $category)
                                @if ($category->countPosts() == 0)
                                    <li class="lvl-1">
                                        <a href="{{route('category.posts',$category->id)}}" class="site-nav lvl-1">{{ $category->name }}
                                        <span class="count">({{ $category->countPosts() }})</span></a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="sidebar-widget clearfix">
                    <div class="widget-title"><h2>پست های اخیر</h2></div>
                        <div class="widget-content">
                            <div class="list list-sidebar-products">
                                @foreach ($post['lastPost'] as $item)
                                    <div class="mini-list-item d-flex align-items-center w-100 clearfix">
                                        <div class="mini-image">
                                            <a class="item-link" href="blog-details.html">
                                            <img
                                                class="featured-image blur-up lazyload"
                                                data-src="{{ $item->image->url }}"
                                                src="{{ $item->image->url }}"
                                                alt="وبلاگ"
                                                width="100"
                                                height="100"
                                            />
                                            </a>
                                        </div>
                                        <div class="me-3 details">
                                            <a class="item-title" href="blog-details.html">{{ $item->title }}</a>
                                            <div class="item-meta opacity-75">
                                                <time datetime="2023-01-02">{{ verta($item->created_at)->format('%d %B %Y') }}</time>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- محتوای وبلاگ-->
        <div class="col-12 col-sm-12 col-md-12 col-lg-9 main-col">
            <div class="blog-article">
                <div class="blog-img mb-3">
                    <img
                        class="rounded-0 blur-up lazyload"
                        data-src="{{ $post['post']->image->url }}"
                        src="{{ $post['post']->image->url }}"
                        alt="مجموعه فروشگاهی جدید فروشگاه ما"
                        width="1200"
                        height="700"/>
                </div>
                <!-- محتوای وبلاگ -->
                <div class="blog-content">
                    <h2 class="h1">{{$post['post']->title}}</h2>
                    <ul class="publish-detail d-flex-wrap">
                        <li>
                            <i class="icon anm anm-clock-r"></i>
                            <time>{{ verta($post['post']->created_at)->format('%d %B %Y') }}</time> 
                        </li>
                        <li>
                            <i class="icon anm anm-comments-l"></i>
                            <a>{{$post['post']->countComment()}} نظر</a>
                        </li>
                        <li>
                            <i class="icon anm anm-tag-r"></i>
                            <span class="opacity-75">دسته بندی</span>
                            <a class="me-1">{{$post['post']->category->name}}</a>
                        </li>
                    </ul>
                    <hr />
                    <div class="content">
                        <p>
                            {!! $post['post']->body !!}
                        </p>
                        <h3>فهرست متن</h3>
                        <ul class="list-styled">
                        <li>
                            قطعه ای از ادبیات کلاسیک لاتین مربوط به 45 قبل از میلاد
                        </li>
                        <li>ادبیات کلاسیک، منبع غیرقابل شک را کشف کرد.</li>
                        <li>
                            اینترنت تمایل دارد در صورت لزوم قطعات از پیش تعریف شده
                            را تکرار کند
                        </li>
                        </ul>
                    </div>
                    <hr/>

                    <!-- نظرات وبلاگ -->
                    @if ($post['post']->comments)
                    <div class="blog-comment section">
                        <h2 class="mb-4">نظرات ({{$post['post']->commentCount()}})</h2>
                        <ol class="comments-list">
                            <li class="comments-items">
                                @foreach ($post['post']->comments()->active()->latest()->get() as $comment)
                                    <div class="comments-item px-0 d-flex w-100">
                                        <div class="flex-shrink-0 comment-img">
                                            <img
                                            class="blur-up lazyload"
                                            data-src="{{asset('front/assets/images/c2487b8c-09ed-4d59-81f0-971ddd5586d9')}}"
                                            src="{{asset('front/assets/images/c2487b8c-09ed-4d59-81f0-971ddd5586d9')}}"
                                            alt=" نظر"
                                            width="200"
                                            height="200"
                                            />
                                        </div>
                                        <div class="flex-grow-1 comment-content">
                                            <div class="comment-user d-flex-center justify-content-between">
                                                <div class="comment-author fw-600">{{$comment->name ?: '...'}}</div>
                                                    <div class="comment-date opacity-75">
                                                        <time datetime="2023-01-02"
                                                        >{{verta($comment->created_at)->format('%d %B %Y')}}
                                                        </time>
                                                    </div>
                                                </div>
                                                <div class="comment-text my-2">
                                                    {{ $comment->body }}
                                                </div>
                                        </div>
                                    </div>
                                    @if ($comment->children)
                                        @foreach ($comment->children as $children)
                                        <div class="comments-item d-flex w-100">
                                            <div class="flex-shrink-0 comment-img">
                                                <img
                                                class="blur-up lazyload"
                                                data-src="{{asset('front/assets/images/c2487b8c-09ed-4d59-81f0-971ddd5586d9')}}"
                                                src="{{asset('front/assets/images/c2487b8c-09ed-4d59-81f0-971ddd5586d9')}}"
                                                alt=" نظر"
                                                width="200"
                                                height="200"
                                                />
                                            </div>
                                            <div class="flex-grow-1 comment-content">
                                                <div class="comment-user d-flex-center justify-content-between">
                                                    <div class="comment-author fw-600">ادمین</div>
                                                        <div class="comment-date opacity-75">
                                                            <time datetime="2023-01-02"
                                                            >{{verta($children->created_at)->format('%d %B %Y')}}
                                                            </time>
                                                        </div>
                                                    </div>
                                                    <div class="comment-text my-2">
                                                        {{ $children->body }}
                                                    </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @endif
                                @endforeach
                            </li>
                        </ol>
                    </div>
                    @endif
                    <div class="formFeilds comment-form form-vertical">  
                        <form id="commentForm" method="post" action="{{ route('comments.store', $post['post']) }}">  
                            @csrf  
                            <h2 class="mb-3">نظر بدهید</h2>  
                            <div class="row">  
                                <div class="col-12 col-sm-12 col-md-6 col-lg-6">  
                                    <div class="form-group">  
                                        <label for="commentName" class="d-none">نام</label>  
                                        <input type="text" id="commentName" name="name" placeholder="نام" value="{{ old('name') }}" required />  
                                    </div>  
                                </div>  
                                <div class="col-12 col-sm-12 col-md-6 col-lg-6">  
                                    <div class="form-group">  
                                        <label for="commentEmail" class="d-none">ایمیل</label>  
                                        <input type="email" id="commentEmail" name="email" placeholder="ایمیل" value="{{ old('email') }}" required />  
                                    </div>  
                                </div>  
                            </div>  
                            <div class="row">  
                                <div class="col-12">  
                                    <div class="form-group">  
                                        <label for="commentMessage" class="d-none">پیام</label>  
                                        <textarea rows="5" id="commentMessage" name="body" placeholder="نوشتن نظر" required>{{ old('body') }}</textarea>  
                                    </div>  
                                </div>  
                            </div>  
                            <div class="row">  
                                <div class="col-12">  
                                    <input type="button" class="btn btn-lg" value="ارسال نظر" />  
                                </div>  
                            </div>  
                        </form>  
                    </div>  
                </div>
            </div>
        </div>
    </div>
</div> 
@endsection
@section('scripts')
<script>  
    $(document).ready(function() {  
        $('#commentForm').on('submit', function(e) {  
            e.preventDefault();  
            let isLoggedIn = @json(auth()->guard('customer')->user());  
            if (!isLoggedIn) {  
                Swal.fire({  
                    icon: "error",  
                    title: "خطای ارسال",  
                    text: "لطفا ابتدا وارد اکانت خود شوید!",  
                    showConfirmButton: false,  
                    footer: '<button class="btn btn-danger"><a href="{{route('pageRegisterLogin')}}">ورود به اکانت</a></button>'  
                });  
            }else{
                var submitBtn = $(this).find('input[type="submit"]');   
                submitBtn.prop('disabled', true); 

                $.ajax({  
                    url: $(this).attr('action'),   
                    type: 'POST',  
                    data: $(this).serialize(),   
                    success: function(response) {  
                        $('#commentForm')[0].reset();  
                        Swal.fire({  
                            icon: "success",  
                            text: "نظر با موفقیت ثبت شد و پس از تایید نمایش داده خواهد شد."  
                        });
                    },  
                    error: function(error) {
                        console.log(error);  
                        Swal.fire({  
                            icon: "error",  
                            text: "خطا در ثبت نظر."  
                        });
                    }  
                    complete: function() {  
                        submitBtn.prop('disabled', false);  
                    }  
                });  
            }
        });  
    });  
</script>  
@endsection
