@extends('admin.layouts.master')

@section('content')
    <div class="page-header">
        @php
            $items = [
                ['title' => 'لیست همه ادمین ها', 'route_link' => 'admin.admins.index'],
                ['title' => 'ویرایش ادمین', 'route_link' => null],
            ];
        @endphp
        <x-breadcrumb :items="$items" />
    </div>

    <x-card>
        <x-slot name="cardTitle">ویرایش ادمین - کد {{ $admin->id }}</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-alert-danger />
            <form action="{{ route('admin.admins.update', $admin) }}" method="POST">

                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-xl-4">
                        <div class="form-group">
                            <label for="name" class="control-label">نام:</label>
                            <input type="text" class="form-control" name="name" id="name"
                                placeholder="نام ادمین اینجا وارد کنید" value="{{ old('name', $admin->name) }}" autofocus>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="form-group">
                            <label for="name" class="control-label">نام کاربری:<span class="text-danger">&starf;</span>
                            </label>
                            <input type="text" class="form-control" name="username" id="name"
                                placeholder="نام کاربری اینجا وارد کنید" value="{{ old('name', $admin->username) }}"
                                required>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="form-group">
                            <label for="label" class="control-label">شماره موبایل:</label>
                            <input type="text" class="form-control" name="mobile" id="mobile"
                                placeholder="شماره موبایل اینجا وارد کنید" value="{{ old('mobile', $admin->mobile) }}">
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="form-group">
                            <label class="control-label">نقش ادمین:</label><span class="text-danger">&starf;</span>
                            <select class="form-control select2">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" @if ($role->name == $adminRolesName) selected @endif>
                                        {{ $role->label ?? $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="form-group">
                            <label class="control-label">کلمه عبور:</label>
                            <input type="password" name="password" class="form-control"
                                placeholder="کلمه عبور را اینجا وارد کنید">
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="form-group">
                            <label class="control-label"> تکرار کلمه عبور:</label>
                            <input type="password" name="password_confirmation" class="form-control"
                                placeholder="کلمه عبور را دوباره اینجا وارد کنید">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="text-center">
                            <button class="btn btn-warning" type="submit">به روزرسانی</button>
                        </div>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-card>
@endsection
