<?php

namespace Modules\Order\Entities;

use Illuminate\Database\Eloquent\Model;
//use Modules\Core\Entities\BaseModel;

class ShippingExcel extends Model
{
    protected $connection = 'extra';
    protected $fillable = [
        'title',
        'barcode',
        'repository',
        'register_date',
        'special_services',
        'destination',
        'reference_number',
        'receiver_name',
        'sender_name',
        'price',
    ];
    public function scopeFilters($query)
    {
        return $query
            ->when(request('name'), function ($q) {
                $q->where('receiver_name', 'LIKE', '%' . request('nanme') . '%')
                    ->orWhere('sender_name', 'LIKE', '%' . request('nanme') . '%');
            })
            ->when(request('destination'), function ($q) {
                $q->where('destination', 'LIKE', '%' . request('destination') . '%');
            })
            ->when(request('start_date'), function ($q) {
                $q->whereDate('created_at', '>=', request('start_date'));
            })
            ->when(request('end_date'), function ($q) {
                $q->whereDate('created_at', '<=', request('end_date'));
            });
    }
}
