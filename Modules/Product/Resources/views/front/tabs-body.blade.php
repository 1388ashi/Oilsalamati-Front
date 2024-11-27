<div class="tabs-listing section pb-0">
    <ul
    class="product-tabs style1 list-unstyled d-flex-wrap d-flex-justify-center d-none d-md-flex"
    >
    <li rel="description" class="active">
        <a class="tablink">توضیحات</a>
    </li>
    <li rel="additionalInformation">
        <a class="tablink">مشخصات</a>
    </li>

    <li rel="reviews"><a class="tablink">بررسی ها</a></li>
    </ul>

    <div class="tab-container">
    <!--Description-->
    <h3 class="tabs-ac-style d-md-none active" rel="description">
        شرح
    </h3>
    <div id="description" class="tab-content">
        <div class="product-description">
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                {!! $product->description !!}
            </div>
        </div>
        </div>
    </div>
    <!--End Description-->

    <!--Additional Information-->
    <h3 class="tabs-ac-style d-md-none" rel="additionalInformation">
        اطلاعات تکمیلی
    </h3>
    <div id="additionalInformation" class="tab-content">
        <div class="product-description">
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-4 mb-md-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle table-part mb-0">
                    @forelse ($product->specifications as $specification)
                    @if ($specification->values)
                    <tr>
                        <th>{{ $specification->label }}</th>
                        <td>
                            @foreach($specification->values as $item)  
                            {{ $item->value }}@if(!$loop->last), @endif  
                            @endforeach
                        </td>
                    </tr>
                    @endif
                    @empty
                    @endforelse
                    </table>
                </div>
            </div>
        </div>
        </div>
    </div>
    <!--End Additional Information-->

    <!--Review-->
    <h3 class="tabs-ac-style d-md-none" rel="reviews">بررسی</h3>
    <div id="reviews" class="tab-content">
        <div class="row">
        <div class="col-12 col-sm-12 col-md-12 col-lg-6 mb-4">
            <div class="ratings-main">
            <div class="avg-rating d-flex-center mb-3">
                <h4 class="avg-mark">{{$averageStar}}</h4>
                <div class="avg-content me-3">
                <p class="text-rating">میانگین امتیاز</p>
                <div class="ratings-full product-review">
                    <a class="reviewLink d-flex-center" href="#reviews">
                        @php($maxStars = 5)  
                        @for ($i = 0; $i < $maxStars; $i++)  
                            @if ($i < $averageStar)  
                                <i class="icon anm anm-star"></i>
                            @else  
                                <i class="icon anm anm-star-o"></i> 
                            @endif  
                        @endfor  
                    </a>
                </div>
                </div>
            </div>
            </div>
            <hr />
            <div class="spr-reviews">
                <h3 class="spr-form-title">نظرات مشتریان</h3>
                <div class="review-inner">
                    @forelse ($product->productComments as $comment)
                    <div class="spr-review d-flex w-100">
                    <div class="spr-review-profile flex-shrink-0">
                        <img
                        class="blur-up lazyload"
                        data-src="{{asset('front/assets/images/c2487b8c-09ed-4d59-81f0-971ddd5586d9')}}"
                        src="{{asset('front/assets/images/c2487b8c-09ed-4d59-81f0-971ddd5586d9')}}"
                        alt=""
                        width="200"
                        height="200"
                        />
                    </div>
                    <div class="spr-review-content flex-grow-1">
                        <div
                        class="d-flex justify-content-between flex-column mb-2"
                        >
                        <div
                            class="title-review d-flex align-items-center justify-content-between"
                        >
                            <h5 class="spr-review-header-title text-transform-none mb-0">
                            {{$comment->creator->first_name || $comment->creator->last_name ? $comment->creator->first_name . ' ' . $comment->creator->last_name : '...' }}
                            </h5>
                            <span class="product-review spr-starratings m-0">
                                <span class="reviewLink">
                                    @php($maxStars = 5)  
                                    @for ($i = 0; $i < $maxStars; $i++)  
                                        @if ($i < $comment->rate)  
                                            <i class="icon anm anm-star"></i>
                                        @else  
                                            <i class="icon anm anm-star-o"></i>
                                        @endif  
                                    @endfor  
                                </span>
                            </span>
                            </div>
                        </div>
                        <b class="head-font">{{$comment->title}}</b>
                        <p class="spr-review-body">{{$comment->body}}</p>
                        </div>
                    </div>
                    @empty
                    <p>نظری برای این محصول ثبت نشده.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-12 col-md-12 col-lg-6 mb-4">
            <form id="commentForm" action="{{ route('product-comments.store') }}" method="post" class="product-review-form new-review-form">  
                @csrf  
                <div id="statusDanger" class="alert alert-danger d-none">  
                    خطا در ثبت نظر.  
                </div>  
                <h3 class="spr-form-title">نظری بنویسید</h3>  
                <fieldset class="row spr-form-contact">  
                    <div class="col-sm-6 spr-form-review-title form-group">  
                        <label class="spr-form-label" for="review">عنوان</label>  
                        <input class="spr-form-input spr-form-input-text" id="review" type="text" name="title" />  
                    </div>  
                    <div class="col-sm-6 spr-form-review-rating form-group">  
                        <label class="spr-form-label">رتبه بندی</label>  
                        <div class="product-review pt-1">  
                            <div class="review-rating">  
                                @for ($i = 0; $i < 5; $i++)  
                                    <span class="star" style="cursor: pointer" data-value="{{ $i + 1 }}">  
                                        <i class="icon anm anm-star-o"></i>  
                                    </span>  
                                @endfor  
                            </div>  
                            <input type="hidden" name="rate" id="rating" value="0">  
                            <input type="hidden" name="product_id" value="{{ $product->id }}">  
                            <input type="hidden" name="show_customer_name" value="1">  
                        </div>  
                    </div>  
                    <div class="col-12 spr-form-review-body form-group">  
                        <label class="spr-form-label" for="message">توضیحات</label>  
                        <div class="spr-form-input">  
                            <textarea class="spr-form-input spr-form-input-textarea" id="message" name="body" rows="3"></textarea>  
                        </div>  
                    </div>  
                </fieldset>  
                <div class="spr-form-actions clearfix">  
                    <input type="button" class="btn btn-primary spr-button spr-button-primary" value="ارسال نظر" />  
                </div>  
            </form>  
        </div>
        </div>
        <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">  
            <div class="modal-dialog modal-md" role="document">  
                <div class="modal-content">  
                    <div class="modal-header">  
                        <h5 class="modal-title font-weight-bold">لطفاً وارد حساب کاربری خود شوید.</h5>  
                        <button type="button" class="close" id="closeButton" aria-label="Close">  
                            <span aria-hidden="true">&times;</span>  
                        </button>  
                    </div>  
                    <div class="modal-body text-center">  
                        <a href="{{ route('pageRegisterLogin') }}" class="btn btn-primary btn-auth">ورود به حساب کاربری</a>  
                        <button type="button" class="btn btn-outline-danger" id="closeButton2">بستن</button>  
                    </div>  
                </div>  
            </div>  
        </div>
    </div>
    <!--End Review-->
    </div>
</div>