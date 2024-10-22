@if($item->status == 'new')
<span title="وضعیت" class="badge badge-warning ">جدید</span>
@elseif($item->status == 'approved')
<span title="وضعیت" class="badge badge-success ">تایید</span>
@else
<span title="وضعیت" class="badge badge-danger  ">حذف شده</span>
@endif
