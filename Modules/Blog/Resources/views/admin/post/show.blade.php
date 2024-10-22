@extends('admin.layouts.master')
@section('content')
  <div class="page-header">
    @php($items = [['title' => 'لیست مطالب', 'route_link' => 'admin.posts.index'],['title' => 'جزئیات مطلب']])
    <x-breadcrumb :items="$items"/>
    <div class="d-flex align-items-center flex-wrap text-nowrap">
      <div class="ml-1">
        @can('modify_post')

          @include('core::includes.edit-icon-button',[
            'model' => $post,
            'route' => 'admin.posts.edit',
            'title' => 'ویرایش مطلب'
          ])

        @endcan
      </div>
      <div>
        <a
          href="{{route('admin.post-comments.index', $post)}}"
          class="btn btn-sm btn-icon btn-info text-white"
          data-toggle="tooltip"
          data-original-title="نظرات">
          مشاهده نظرات
          <i class="fa fa-comment mr-1"></i>
        </a>
      </div>
      <div class="mr-1">
        @can('delete_post')
          @include('core::includes.delete-icon-button',[
            'model' => $post,
            'route' => 'admin.posts.destroy',
            'disabled' => !$post->isDeletable(),
            'title' => 'حذف مطلب'
          ])
        @endcan
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-lg-6">
          <ul class="list-group">
            <li class="list-group-item"><strong class="fs-14">کد: </strong> {{ $post->id }} </li>
            <li class="list-group-item"><strong class="fs-14">عنوان: </strong> {{ $post->title }} </li>
            <li class="list-group-item"><strong class="fs-14">دسته
                بندی: </strong> {{ $post->category->name }} </li>
            <li class="list-group-item"><strong class="fs-14">اسلاگ: </strong> {{ $post->slug }} </li>
            <li class="list-group-item"><strong class="fs-14">تعداد بازید: </strong> {{ $post->views_count }} </li>
          </ul>
        </div>
        <div class="col-lg-6">
          <ul class="list-group">
            <li class="list-group-item"><strong class="fs-14">تعداد کامنت
                ها: </strong> {{ $post->comments_count }}
            </li>
            <li class="list-group-item">
              <strong class="fs-14">وضعیت: </strong>
              <span class="badge badge-{{ $post->getStatusColorAttribute() }}">
                {{ config('blog.statuses.' . $post->status) }}
              </span>
            </li>
            <li class="list-group-item"><strong class="fs-14">ویژه: </strong>
              <span
                class="text-{{ $post->special ? 'success' : 'danger' }}"> {{ $post->special ? 'هست' : 'نیست' }} </span>
            </li>
            <li class="list-group-item"><strong class="fs-14">تاریخ
                ثبت: </strong> {{ verta($post->created_at)->format('Y/m/d') }} </li>
            <li class="list-group-item"><strong class="fs-14">تاریخ
                انتشار: </strong> {{ verta($post->published_at)->format('Y/m/d') }} </li>
          </ul>
        </div>

        @if($post->summary)
          <div class="col-12 mt-5">
            <ul class="list-group">
              <li class="list-group-item px-2">
                <p class="fs-16 font-weight-bold header pr-2">خلاصه توضیحات: </p>
                {{ $post->summary}}
              </li>
            </ul>
          </div>
        @endif

        @if($post->body)
          <div class="col-12 mt-5">
            <ul class="list-group">
              <li class="list-group-item px-2">
                <p class="fs-16 font-weight-bold header pr-2">متن: </p>
                {!! $post->body !!}
              </li>
            </ul>
          </div>
        @endif

        @if($post->meta_description)
          <div class="col-12 mt-5">
            <ul class="list-group">
              <li class="list-group-item px-2">
                <p class="fs-16 font-weight-bold header pr-2">توضیحات متا: </p>
                {{ $post->meta_description }}
              </li>
            </ul>
          </div>
        @endif

      </div>
    </div>
  </div>
@endsection
