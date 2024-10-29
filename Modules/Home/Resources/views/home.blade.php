@extends('front.layouts.master')

@section('title')
	<title>صفحه اصلی</title>
@endsection

@section('body_class') template-index index-demo1 @endsection

@section('content')

@include('home::includes.sliders')
@include('home::includes.special-categories')
@include('home::includes.most-sale')
@include('home::includes.new-products')
@include('home::includes.banner')
@include('home::includes.packages')
@include('home::includes.most-visited')
@include('home::includes.posts')
@include('home::includes.services')

@endsection
