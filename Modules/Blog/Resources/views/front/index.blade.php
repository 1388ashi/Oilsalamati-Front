@extends('front.layouts.master')
@section('content')
<x-front.breadcrumb :items="[['title' => 'وبلاگ']]" />

<div class="container">
    <div class="row">
        <div class="col-12 col-sm-12 col-md-12 col-lg-3 blog-sidebar sidebar sidebar-bg">
            <div class="sidebar-tags sidebar-sticky clearfix">
                <div class="sidebar-widget clearfix categories">
                    <div class="widget-title">
                        <h2>دسته</h2></div>
                    <div class="widget-content">
                        <ul class="sidebar-categories scrollspy clearfix">
                            @foreach ($data['category'] as $category)
                            <li class="lvl-1">
                                <a href="{{route('category.posts',$category->id)}}" class="site-nav lvl-1">{{ $category->name }}
                                <span class="count">({{ $category->countPosts() }})</span></a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="sidebar-widget clearfix">
                    <div class="widget-title"><h2>پست های اخیر</h2></div>
                        <div class="widget-content">
                            <div class="list list-sidebar-products">
                                @foreach ($data['posts']->take(3) as $post)
                                    <div class="mini-list-item d-flex align-items-center w-100 clearfix">
                                        <div class="mini-image">
                                            <a class="item-link" href="blog-details.html">
                                            <img
                                                class="featured-image blur-up lazyload"
                                                data-src="{{ asset('front/assets/images/blog/post-img3-100x.jpg') }}"
                                                src="{{ asset('front/assets/images/blog/post-img3-100x.jpg') }}"
                                                alt="وبلاگ"
                                                width="100"
                                                height="100"
                                            />
                                            </a>
                                        </div>
                                        <div class="me-3 details">
                                            <a class="item-title" href="blog-details.html">{{ $post->title }}</a>
                                            <div class="item-meta opacity-75">
                                                <time datetime="2023-01-02">{{ verta($post->created_at)->format('%d %B %Y') }}</time>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                            </div>
                        </div>
                    </div>
                </div>
        </div>

        <div class="col-12 col-sm-12 col-md-12 col-lg-9 main-col">
            <!--Toolbar-->
            <div class="toolbar toolbar-wrapper blog-toolbar">
                <div class="row align-items-center">
                    <div class="col-12 col-sm-6 col-md-6 col-lg-6 text-right filters-toolbar-item d-flex justify-content-center justify-content-sm-start">
                        <div class="search-form mb-3 mb-sm-0">
                            <form class="d-flex" action="{{route('posts')}}">
                            <input
                                class="search-input"
                                name="title"
                                type="text"
                                value="{{request('title')}}"
                                placeholder="جستجوی وبلاگ..."
                            />
                            <button type="submit" class="search-btn">
                                <i class="icon anm anm-search-l"></i>
                            </button>
                            </form>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-6 col-lg-6 text-left filters-toolbar-item d-flex justify-content-between justify-content-sm-end">
                        <form action="{{route('posts')}}" method="GET">  
                            <div class="filters-item d-flex align-items-center me-3">  
                                <label for="SortBy" class="mb-0 ms-2">مرتب‌سازی:</label>  
                                <select name="sortBy" id="SortBy" class="filters-toolbar-sort" onchange="this.form.submit();">  
                                    <option value="new">جدیدترین</option>  
                                    <option value="special" {{ request('sortBy') == 'special' ? 'selected' : '' }}>ویژه</option>  
                                    <option value="most-comments" {{ request('sortBy') == 'most-comments' ? 'selected' : '' }}>بیشترین نظرات</option>  
                                </select>  
                            </div>  
                        </form>  
                    </div>
                </div>
            </div>
            <!--End Toolbar--><!--Blog Grid-->
            <div class="blog-grid-view">
                <div class="row col-row row-cols-lg-2 row-cols-sm-2 row-cols-1">
                    @forelse ($data['posts'] as $post)
                        <div class="blog-item col-item">
                            <div class="blog-article zoomscal-hov">
                                <div class="blog-img">
                                    <a
                                        class="featured-image rounded-0 zoom-scal"
                                        href="blog-details.html"
                                        ><img
                                        class="rounded-0 blur-up lazyload"
                                        data-src="{{ asset('front/assets/images/blog/post-img1.jpg') }}"
                                        src="{{ asset('front/assets/images/blog/post-img1.jpg') }}"
                                        alt="مجموعه فروشگاه جدید فروشگاه ما"
                                        width="740"
                                        height="410"
                                    />
                                    </a>
                                </div>
                                <div class="blog-content">
                                    <h2 class="h3">
                                        <a href="{{ route('posts.show',$post->id) }}">{{$post->title}}</a>
                                    </h2>
                                    <ul class="publish-detail d-flex-wrap">
                                        {{-- <li>
                                            <i class="icon anm anm-user-al"></i>
                                            <span class="opacity-75 ms-1">
                                                ارسال شده توسط:</span
                                            >
                                            کاربر
                                        </li> --}}
                                        <li>
                                            <i class="icon anm anm-clock-r"></i>
                                            <time>{{ verta($post->created_at)->format('%d %B %Y') }}</time> 
                                            </li>
                                            <li><i class="icon anm anm-comments-l"></i><a href="#">{{$post->countComment()}} نظر</a></li>
                                    </ul>
                                    <p class="content">{{ Str::limit($post->summary, 150) }}</p>
                                    <a
                                        href="{{ route('posts.show',$post->id) }}"
                                        class="btn btn-secondary btn-sm"
                                        >بیشتر بخوانید</a
                                    >
                                </div>
                            </div>
                        </div>
                    @empty
                        <p>بلاگی پیدا نشد</p>
                    @endforelse
                </div>

                <!-- صفحه بندی -->
                
                <nav class="clearfix pagination-bottom">
                    <ul class="pagination justify-content-center">
                        {{ $data['posts']->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}
                    {{-- <li class="page-item disabled">
                        <a class="page-link" href="#"
                        ><i class="icon anm anm-angle-right-l"></i
                        ></a>
                    </li>
                    <li class="page-item active">
                        <a class="page-link" href="#">1</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">2</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link dot" href="#">...</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">5</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#"
                        ><i class="icon anm anm-angle-left-l"></i
                        ></a>
                    </li> --}}
                    </ul>
                </nav>
            <!-- پایان صفحه بندی -->
            </div>
            <!--End Blog Grid-->
        </div>
    </div>
</div>
@endsection