@extends('admin.layouts.master')

@section('content')
    <div class="page-header">
        @php($items = [['title' => 'گروه منو ها']])
        <x-breadcrumb :items="$items" />
    </div>

    <x-card>
        <x-slot name="cardTitle">گروه های منو</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <div class="row">
                <a style="border: 1px solid #dadada;border-radius: 15px;box-shadow: 0 0 10px #dadada;padding: 3rem 1.5rem;"
                    class="col-md-3 slider-group text-center ml-4" href="{{ url('/admin/menu/1') }}">
                    <p style="font-size: 17px;font-weight: 300;" class="d-block my-3 font-bold">هدر</p>
                </a>
                <a style="border: 1px solid #dadada;border-radius: 15px;box-shadow: 0 0 10px #dadada;padding: 3rem 1.5rem;"
                    class="col-md-3 slider-group text-center ml-4" href="{{ url('/admin/menu/2') }}">
                    <p style="font-size: 17px;font-weight: 300;" class="d-block my-3 font-bold">فوتر</p>
                </a>
            </div>
        </x-slot>
    </x-card>
@endsection
