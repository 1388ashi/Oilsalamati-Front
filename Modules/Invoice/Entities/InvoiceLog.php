<?php

namespace Modules\Invoice\Entities;

use Illuminate\Database\Eloquent\Model;
//use Modules\Core\Entities\BaseModel;

class InvoiceLog extends Model
{
    protected $fillable = [
        'description',
        'back_price',
        'shipping_amount',
        'item_amount',
        'customer_id',
        'item_id',
        'invoice_id',
    ];

    public function invoices()
    {
        return $this->belongsTo(Invoice::class);
    }

}
