
@extends('admin.layouts.master')

@section('content')
    <div class="card">
        <div class="card-body">
{{--            @php--}}
{{--                $tableTh= "--}}
{{--                <tr>--}}
{{--                    <td>ردیف</td>--}}
{{--                    <td>شتاسه</td>--}}
{{--                    <td>اسم استان</td>--}}
{{--                <tr>";--}}
{{--                $tableTd = '';--}}
{{--                $counter = 1;--}}
{{--                foreach ($provinces as $province){--}}

{{--                        $tableTd = "$tableTd<tr>--}}
{{--                        <td>$counter</td>--}}
{{--                        <td>$province->id</td>--}}
{{--                        <td>$province->name</td>--}}
{{--                        </tr>";--}}
{{--                }--}}
{{--            @endphp--}}
{{--            @include('components.table')--}}
            <x-table-component>
                <x-slot name="tableTh">

                </x-slot>
                <x-slot name="tableTd">

                </x-slot>

            </x-table-component>
        </div>
    </div>
@endsection
