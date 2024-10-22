@if($newsletter->status == 'pending')
<span title="وضعیت" class="badge badge-warning-light ">در حال بررسی</span>
@elseif($newsletter->status == 'success')
<span title="وضعیت" class="badge badge-success-light ">ارسال شده</span>
@else
<span title="وضعیت" class="badge badge-danger-light  ">خطا</span>
@endif
