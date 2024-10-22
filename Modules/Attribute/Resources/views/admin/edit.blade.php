@extends('admin.layouts.master')
@section('content')
    <div class="page-header">
        @php
            $items = [
                ['title' => 'لیست ویژگی ها', 'route_link' => 'admin.attributes.index'],
                ['title' => 'ویرایش ویژگی', 'route_link' => null],
            ];
        @endphp
        <x-breadcrumb :items="$items" />
    </div>

    <x-card>
        <x-slot name="cardTitle">ویرایش ویژگی - کد {{ $attribute->id }}</x-slot>
        <x-slot name="cardOptions"><x-card-options /></x-slot>
        <x-slot name="cardBody">
            <x-alert-danger />
            <form action="{{ route('admin.attributes.update', $attribute) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="name" class="control-label"> نام: <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="name" class="form-control" name="name"
                                value="{{ old('name', $attribute->name) }}" required autofocus />
                            <span class="text-muted-dark mt-2 mr-1 font-weight-bold fs-11">نام ویژگی را حتما به <span
                                    class="text-danger">انگیلیسی</span> وارد کنید!</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="label" class="control-label"> لیبل: <span
                                    class="text-danger">&starf;</span></label>
                            <input type="text" id="label" class="form-control" name="label"
                                value="{{ old('label', $attribute->label) }}" required />
                            <span class="text-muted-dark mt-2 mr-1 font-weight-bold fs-11">لیبل ویژگی را حتما به <span
                                    class="text-danger">فارسی</span> وارد کنید!</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="type" class="control-label">نحوه نمایش: <span
                                    class="text-danger">&starf;</span></label>
                            <select name="style" class="form-control" required>
                                <option value="select" @if ($attribute->style == 'select') selected @endif>کوبمو</option>
                                <option value="box" @if ($attribute->style == 'box') selected @endif>مربعی</option>
                                <option value="image" @if ($attribute->style == 'image') selected @endif>مربعی با عکس
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="type" class="control-label"> انتخاب نوع ویژگی: </label>
                            <select class="form-control" name="type" id="typeattr">
                                @foreach ($types as $type)
                                    <option value="{{ $type }}"
                                        {{ old('type', $attribute->type) == $type ? 'selected' : null }}>
                                        {{ config('attribute.types.' . $type) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="status" value="1"
                                    {{ old('status', $attribute->status) == 1 ? 'checked' : null }} />
                                <span class="custom-control-label">وضعیت</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="show_filter" value="1"
                                    {{ old('show_filter', $attribute->show_filter) == 1 ? 'checked' : null }} />
                                <span class="custom-control-label">نمایش در فیلتر</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row my-2" id="attribute-values-section"
                    style="display: {{ $attribute->values->isEmpty() ? 'none' : 'flex' }};">
                    <div class="col-12">
                        <p class="header pr-2 font-weight-bold fs-22">مقادیر ویژگی</p>
                    </div>
                    <div class="col-12" id="attribute-values-group">
                        <div class="row" id="attribute-values-group-row">
                            @foreach ($attribute->values as $index => $specValue)
                                <div class="col-3 d-flex positive-negative-container mt-2">
                                    <button type="button" class="positive-btn btn btn-success ml-1">+</button>
                                    <button type="button" class="negative-btn btn btn-danger ml-1">-</button>
                                    <input name="values[{{ $index }}]" type="text"
                                        value="{{ $specValue->value }}" class="form-control mx-1" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col">
                        <div class="d-flex justify-content-center align-items-center" style="gap: 10px;">
                            <button class="btn btn-warning" type="submit">ویرایش و ذخیره</button>
                            <a class="btn btn-outline-danger" href="{{ route('admin.attributes.index') }}">برگشت</a>
                        </div>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-card>
@endsection
@section('scripts')
    <script>
        $(document).ready(() => {

            let counter = 1000;
            let attributeValuesGroupRow = $('#attribute-values-group-row');
            let attributeValuesSection = $('#attribute-values-section');
            let negativeButton =
                '<button id="negative-btn" type="button" class="negative-btn btn btn-danger ml-1">-</button>';
            let positiveButton =
                '<button id="positive-btn" type="button" class="positive-btn btn btn-success ml-1">+</button>';

            let hasValue = {
                text: false,
                select: true,
            };

            if ($('#typeattr').val() == 'text') {
                attributeValuesGroupRow.append(`
    <div class="col-3 d-flex plus-negative-container mt-2">
      <button id="positive-btn-0" type="button" class="positive-btn btn btn-success ml-1">+</button>
      <button id="negative-btn-0" type="button" class="negative-btn btn btn-danger ml-1" disabled>-</button>
      <input id="value-0" name="values[0]" type="text" placeholder="مقدار" class="form-control mx-1">
    </div>
  `);
            }

            $('#typeattr').on('input', () => {
                let type = $('#typeattr').val();
                if (hasValue[type]) {
                    $('#attribute-values-section').css('display', 'flex');
                } else {
                    $('#attribute-values-section').css('display', 'none');
                }
            });

            attributeValuesGroupRow.on('click', '.positive-btn', (event) => {

                let newPositiveBtn = $(positiveButton).clone();
                let newNegativeBtn = $(negativeButton).clone()

                let newInput = $(
                    `<input
      name="values[${counter}]"
      type="text"
      placeholder="مقدار"
      class="form-control mx-1"
    />`
                );

                let newGroup = $('<div class="col-3 d-flex positive-negative-container mt-2"></div>');

                newGroup
                    .append(newPositiveBtn)
                    .append(newNegativeBtn)
                    .append(newInput);

                attributeValuesGroupRow.append(newGroup);

                counter++;

            });

            attributeValuesGroupRow.on('click', '.negative-btn', (event) => {
                $(event.currentTarget).closest('.positive-negative-container').remove();
                counter--;
            });

        });
    </script>
@endsection
