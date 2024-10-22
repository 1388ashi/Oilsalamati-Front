@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        <x-breadcrumb :items="[['title' => 'تنظیمات']]" />
    </div>

    <x-card>
        <x-slot name="cardTitle">تنظیمات</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            @include('components.errors')
            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    @php($types = ['text', 'string', 'integer', 'number', 'email'])
                    @foreach ($settingTypes as $type => $settings)
                        @if (in_array($type, $types))
                            @foreach ($settings as $setting)
                                <div class="col-md-6 col-12 my-1">
                                    <div class="form-group">
                                        <label class="form-label font-weight-bold"
                                            for="{{ $setting->name }}">{{ $setting->label }} :</label>
                                        <input id="{{ $setting->name }}" type="{{ $type }}"
                                            name="{{ $setting->name }}" class="form-control" value="{{ $setting->value }}"
                                            @if ($type == 'number' or $type == 'integer') min="0" @endif />
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        @if ($type == 'image')
                            @foreach ($settings as $setting)
                                <div class="col-md-6 col-12 my-1">
                                    <div class="form-group">
                                        <label class="form-label font-weight-bold"
                                            for="{{ $setting->name }}">{{ $setting->label }} :</label>
                                        <input id="{{ $setting->name }}" value="{{ $setting->value }}" type="file"
                                            name="{{ $setting->name }}" class="form-control" />
                                    </div>
                                </div>
                                <div class="col-md-6 col-12 my-1">
                                    <button type="button" class="btn btn-danger btn-sm"
                                        onclick="confirmDelete('delete-image-{{ $setting->id }}')">
                                        <i class="fe fe-trash"></i>
                                    </button>
                                    <br>
                                    <figure class="figure">
                                        <img src="{{ Storage::url($setting->value) }}" class="img-thumbnail" width="50"
                                            height="50" alt="{{ $setting->label }}" />
                                    </figure>
                                </div>
                            @endforeach
                        @endif
                        @if ($type == 'textarea')
                            @foreach ($settings as $setting)
                                <div class="col-12 my-1">
                                    <div class="form-group">
                                        <label class="form-label font-weight-bold"
                                            for="{{ $setting->name }}">{{ $setting->label }} :</label>
                                        <textarea class="form-control" name="{{ $setting->name }}" id="{{ $setting->name }}">{!! $setting->value !!}</textarea>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endforeach
                </div>
                <button class="btn btn-warning" type="submit">بروزرسانی</button>
            </form>
            @foreach ($settingTypes as $type => $settings)
                @if ($type == 'image')
                    @foreach ($settings as $setting)
                        <form action="{{ route('admin.settings.destroy-file', $setting) }}"
                            id="delete-image-{{ $setting->id }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach
                @endif
            @endforeach
        </x-slot>
    </x-card>
@endsection
