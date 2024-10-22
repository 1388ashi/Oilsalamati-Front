<div class="table-responsive">
    <table class="table table-striped table-bordered text-nowrap text-center" @isset($id) id="{{ $id }}" @endisset>
        <thead class="border-top">
        {{$tableTh}}
        </thead>
        <tbody>
        {{ $tableTd }}
        </tbody>
    </table>
    @isset($extraData)
    {{ $extraData }}
    @endisset
</div>
