<?php

namespace Modules\Shipping\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Modules\Core\Entities\BaseModel;
use Modules\Core\Helpers\Helpers;

class ShippingRange extends Model
{
    protected $fillable = [
        'lower', 'higher', 'shipping_id', 'amount'
    ];
    protected $hidden = ['created_at', 'updated_at'];

    public static function booted()
    {
        static::creating(function (ShippingRange $shippingRange) {
            $shippingRange->checkDuplicateRanges();
        });
        static::updating(function (ShippingRange $shippingRange) {
            $shippingRange->checkDuplicateRanges();
        });
    }

    // Called in validations and changes
    public function checkDuplicateRanges()
    {
        $alreadyExists = ShippingRange::query()
            ->where('shipping_id', $this->shipping_id)
            ->where('lower', '<', $this->lower)
            ->where('higher', '>', $this->lower)->exists();
        $alreadyExists2 = ShippingRange::query()
            ->where('shipping_id', $this->shipping_id)
            ->where('higher', '>', $this->higher)
            ->where('lower', '<', $this->higher)->exists();
        if ($alreadyExists || $alreadyExists2) {
            throw Helpers::makeValidationException('این بازه قبلا انتخاب شده است');
        }
    }

    public function shipping()
    {
        return $this->belongsTo(Shipping::class);
    }

    public static function store(Request $request, ShippingRange $shippingRange = null)
    {
        $shippingRange = $shippingRange ?: new ShippingRange();
        $shippingRange->fill($request->all());
        $shippingRange->save();
        $shippingRange->load('shipping');

        return $shippingRange;
    }
}
