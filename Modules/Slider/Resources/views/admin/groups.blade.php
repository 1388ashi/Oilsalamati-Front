@extends('admin.layouts.master')

@section('content')
    <div class="page-header">
        @php($items = [['title' => 'گروه اسلایدر ها']])
        <x-breadcrumb :items="$items" />
    </div>
    <!-- row opened -->
    <x-card>
        <x-slot name="cardTitle">گروه های اسلایدر</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <div class="row">
                <a style="border: 1px solid #dadada;border-radius: 15px;box-shadow: 0 0 10px #dadada;padding: 3rem 1.5rem;"
                    class="col-md-3 slider-group text-center ml-4" href="{{ url('/admin/sliders/groups/header') }}">
                    <p style="font-size: 17px;font-weight: 300;" class="d-block my-3 font-bold">دسکتاپ</p>
                </a>
                <a style="border: 1px solid #dadada;border-radius: 15px;box-shadow: 0 0 10px #dadada;padding: 3rem 1.5rem;"
                    class="col-md-3 slider-group text-center ml-4" href="{{ url('/admin/sliders/groups/header-mobile') }}">
                    <p style="font-size: 17px;font-weight: 300;" class="d-block my-3 font-bold">موبایل</p>
                </a>
            </div>
        </x-slot>
    </x-card>
@endsection
