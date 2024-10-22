@extends('admin.layouts.master')
@section('content')

    <div class="page-header">
        @php($items = [['title' => 'لیست نظرات محصولات']])
        <x-breadcrumb :items="$items" />
    </div>

    <x-card>
        <x-slot name="cardTitle">جستجوی پیشرفته</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <form action="{{ route('admin.product-comments.index') }}" method="GET">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label> انتخاب محصول: </label>
                            <select name="product_id" class="form-control search-product-ajax"></select>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>انتخاب وضعیت :</label>
                            <select class="form-control" name="status" id="StatusSelect">
                                <option value="">انتخاب</option>
                                <option value="all" {{ request('status') == 'all' ? 'selected' : null }}>همه</option>
                                @foreach (config('productcomment.statuses') as $name => $label)
                                    <option value="{{ $name }}"
                                        {{ request('status') == $name ? 'selected' : null }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="start_date_show">از تاریخ :</label>
                            <input class="form-control fc-datepicker" id="start_date_show" type="text"
                                autocomplete="off" />
                            <input name="start_date" id="start_date_hide" type="hidden"
                                value="{{ request('start_date') }}" />
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="end_date_show">تا تاریخ :</label>
                            <input class="form-control fc-datepicker" id="end_date_show" type="text"
                                autocomplete="off" />
                            <input name="end_date" id="end_date_hide" type="hidden" value="{{ request('end_date') }}" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-9 col-lg-8 col-md-6 col-12">
                        <button class="btn btn-primary btn-block" type="submit">جستجو <i class="fa fa-search"></i></button>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                        <a href="{{ route('admin.product-comments.index') }}" class="btn btn-danger btn-block">حذف همه فیلتر
                            ها <i class="fa fa-close"></i></a>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-card>

    <x-card>
        <x-slot name="cardTitle">نظرات محصولات</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-table-component>
                <x-slot name="tableTh">
                    <tr>
                        <th>ردیف</th>
                        <th>عنوان</th>
                        <th>نظر</th>
                        <th>امتیاز</th>
                        <th>موبایل کاربر</th>
                        <th>محصول</th>
                        <th>وضعیت</th>
                        <th>تاریخ ثبت</th>
                        <th>عملیات</th>
                    </tr>
                </x-slot>
                <x-slot name="tableTd">
                    @forelse ($comments as $comment)
                        <tr>
                            <td class="font-weight-bold">{{ $loop->iteration }}</td>
                            <td style="white-space: wrap;">{{ $comment->title ?? '-' }}</td>
                            <td style="white-space: wrap;">{{ $comment->body }}</td>
                            <td>
                                <span>
                                    @if ($comment->rate > 0)
                                        @for ($i = 1; $i <= (int) $comment->rate; $i++)
                                            <i class="fa fa-star text-warning"></i>
                                        @endfor
                                    @endif
                                    @php($remainingRate = 5 - (int) $comment->rate)
                                    @if ($remainingRate > 0)
                                        @for ($i = 1; $i <= $remainingRate; $i++)
                                            <i class="fa fa-star-o text-warning"></i>
                                        @endfor
                                    @endif
                                </span>
                            </td>
                            <td>{{ $comment->creator->mobile }}</td>
                            <td style="white-space: wrap;">{{ $comment->product->title }}</td>
                            <td>
                                <x-badge isLight="true">
                                    <x-slot
                                        name="type">{{ config('comment.status_color.' . $comment->status) }}</x-slot>
                                    <x-slot name="text">{{ config('comment.statuses.' . $comment->status) }}</x-slot>
                                </x-badge>
                            </td>
                            <td>{{ verta($comment->created_at)->format('Y/m/d H:i') }}</td>
                            <td>
                                <button class="btn btn-sm btn-primary btn-icon text-white"
                                    data-target="#show-comment-detail-modal-{{ $comment->id }}" data-toggle="modal">
                                    <i class="fa fa-eye"></i>
                                </button>
                                @can('delete_comment')
                                    @include('core::includes.delete-icon-button', [
                                        'model' => $comment,
                                        'route' => 'admin.product-comments.destroy',
                                    ])
                                @endcan
                            </td>
                        </tr>
                    @empty
                        @include('core::includes.data-not-found-alert', ['colspan' => 9])
                    @endforelse
                </x-slot>
                <x-slot name="extraData">
                    {{ $comments->onEachSide(0)->links('vendor.pagination.bootstrap-4') }}
                </x-slot>
            </x-table-component>
        </x-slot>
    </x-card>

    @foreach ($comments as $comment)
        <x-modal id="show-comment-detail-modal-{{ $comment->id }}" size="lg">
            <x-slot name="title">مشاهده نظر کد - {{ $comment->id }}</x-slot>
            <x-slot name="body">
                <div class="row">
                    <div class="col-12 my-1">
                        <strong class="fs-17">کاربر :</strong>
                        <span class="fs-16">{{ $comment->creator->full_name ?? $comment->creator->mobile }}</span>
                    </div>
                    <div class="col-12 my-1">
                        <strong class="fs-17">محصول :</strong>
                        <span class="fs-16">{{ $comment->product->title }}</span>
                    </div>
                    <div class="col-12 my-1">
                        <strong class="fs-17">امتیاز :</strong>
                        <span class="fs-16">
                            @if ($comment->rate > 0)
                                @for ($i = 1; $i <= (int) $comment->rate; $i++)
                                    <i class="fa fa-star text-warning"></i>
                                @endfor
                            @endif
                            @php($remainingRate = 5 - (int) $comment->rate)
                            @if ($remainingRate > 0)
                                @for ($i = 1; $i <= $remainingRate; $i++)
                                    <i class="fa fa-star-o text-warning"></i>
                                @endfor
                            @endif
                        </span>
                    </div>
                    <div class="col-12 my-1">
                        <strong class="fs-17">نظر :</strong>
                        <span class="fs-16">{{ $comment->body }}</span>
                    </div>
                </div>
                <form action="{{ route('admin.product-comments.assign-status') }}" method="POST"
                    id="assign-status-form-{{ $comment->id }}">
                    @csrf
                    <input type="hidden" value="{{ $comment->id }}" name="id">
                    <input type="hidden" value="" name="status" id="status-{{ $comment->id }}">
                </form>
            </x-slot>
            <x-slot name="footer">
                <div class="d-flex justify-content-center my-2">
                    <button class="btn btn-success mx-1" onclick="assignStatus('approved', '{{ $comment->id }}')">تایید
                        نظر</button>
                    <button class="btn btn-danger mx-1" onclick="assignStatus('reject', '{{ $comment->id }}')">رد
                        نظر</button>
                    <button class="btn btn-warning mx-1" onclick="assignStatus('pending', '{{ $comment->id }}')">در
                        انتظار بررسی</button>
                </div>
            </x-slot>
        </x-modal>
    @endforeach

@endsection
@section('scripts')
    @include('core::includes.date-input-script', [
        'dateInputId' => 'start_date_hide',
        'textInputId' => 'start_date_show',
    ])

    @include('core::includes.date-input-script', [
        'dateInputId' => 'end_date_hide',
        'textInputId' => 'end_date_show',
    ])

    <script>
        function assignStatus(status, commentId) {
            $('#status-' + commentId).attr('value', status);
            $('#assign-status-form-' + commentId).submit();
        }

        $('#StatusSelect').select2({
            placeholder: 'انتخاب وضعیت'
        });
    </script>

    <script>
        $('.search-product-ajax').select2({
            ajax: {
                url: '{{ route('admin.products.search') }}',
                dataType: 'json',
                processResults: (response) => {
                    let products = response.data.products || [];

                    return {
                        results: products.map(product => ({
                            id: product.id,
                            title: product.title,
                        })),
                    };
                },
                cache: true,
            },
            placeholder: 'عنوان محصول را وارد کنید',
            templateResult: (repo) => {
                if (repo.loading) {
                    return "در حال بارگذاری...";
                }

                let $container = $(
                    "<div class='select2-result-repository clearfix'>" +
                    "<div class='select2-result-repository__meta'>" +
                    "<div class='select2-result-repository__title'></div>" +
                    "</div>" +
                    "</div>"
                );

                let text = repo.title;
                $container.find(".select2-result-repository__title").text(text);

                return $container;
            },
            minimumInputLength: 1,
            templateSelection: (repo) => {
                return repo.id ? repo.title : repo.text;
            }
        });
    </script>
@endsection
