<?php

namespace Modules\Shipping\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Modules\Area\Entities\City;
use Modules\Area\Entities\Province;
use Modules\Core\Classes\CoreSettings;
//use Modules\Core\Entities\BaseModel;
use Modules\Core\Entities\HasCommonRelations;
use Modules\Core\Entities\HasFilters;
use Modules\Core\Entities\Media;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Services\Cache\CacheForgetService;
use Modules\Core\Services\Media\MediaDisplay;
use Modules\Core\Traits\HasAuthors;
use Modules\Core\Traits\InteractsWithMedia;
use Modules\Customer\Entities\Address;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomerRole;
use Modules\Order\Entities\Order;
use Modules\Product\Entities\Variety;
use Modules\Setting\Entities\Setting;
use Modules\Shipping\Services\ShippingCalculatorService;
//use Shetabit\Shopit\Modules\Shipping\Database\factories\ShippingFactory;
//use Shetabit\Shopit\Modules\Shipping\Entities\Shipping as BaseShipping;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;

class Shipping extends Model implements Sortable, HasMedia
{

    protected $fillable = [
        'minimum_delay',
        'name',
        'default_price',
        'free_threshold',
        'order',
        'description',
        'status',
        'packet_size',
        'first_packet_size',
        'more_packet_price',
        'free_shipping'
    ];

//    protected $with = ['shippingRanges'];
    protected $hidden = ['media','created_at', 'updated_at','creator_id','updater_id'];

    public function shippingRanges()
    {
        return $this->hasMany(ShippingRange::class)
            ->orderBy('lower', 'asc');
    }

//    city, weight, quantity => price
    public function getPrice(City $city, int $orderAttributeVarietyWeight, $newQuantity=1, $morePacketPrice =null, $firstPacketSize=null, $customer_id = null)
    {
        $customer_id = $customer_id ?? Auth::user()->id;
        $hasFreeShipping = (new \Modules\Core\Helpers\Helpers)->getShippingAmountByOrderAmount($customer_id);
        if ($hasFreeShipping){
            // در صورتی که مبلغ سبد خرید مشتری از میزان تعیین شده برای سطح وی بیشتر باشد، هزینه ارسال 0 درنظر گرفته می شود
            return 0;
        }

        //GET shipping range
        $shippingRanges = $this->shippingRanges->filter(function ($shippingRange) use ($orderAttributeVarietyWeight) {
            return $shippingRange->lower <= $orderAttributeVarietyWeight && $shippingRange->higher >= $orderAttributeVarietyWeight;
        });

        if ($shippingRanges->count()) {
            return $shippingRanges->first()->amount;
        }

        return $this->default_price;

    }

    //use in order model
///////////////////////////////////////////////////////////////////////////
//use in order model when add item
    public function getOldWeight($parentOrder)
    {
        $OldOrderWeight = 0;
        $childOfParentOrdersWeight = 0;

        //xweight = default product weight
        $xweight = Setting::getFromName('defualt_product_weight') ? Setting::getFromName('defualt_product_weight') : 120;

        //for add Item
        //Calculate Old Shipping Weight

        foreach ($parentOrder->items as $orderItem){
            if ($orderItem->status == 1){
                $variety = Variety::find($orderItem->variety_id);

                if($variety?->weight){
                    $xweight = $variety->weight;
                }elseif($variety->product?->weight){
                    $xweight = $variety->product->weight;
                }
            }

            $OldOrderWeight = $OldOrderWeight + ($orderItem->quantity * $xweight);
        }

        $childOfParentOrders = Order::query()
            ->where('parent_id',$parentOrder->id)
            ->where('status','new')
            ->get();

        if ($childOfParentOrders){
            foreach ($childOfParentOrders as $childOfParentOrder){
                foreach ($childOfParentOrder->items as $orderItem){
                    if ($orderItem->status == 1){
                        $variety = Variety::find($orderItem->variety_id);
                        if($variety?->weight){
                            $xweight = $variety->weight;
                        }elseif($variety->product?->weight){
                            $xweight = $variety->product->weight;
                        }
                    }
                    $childOfParentOrdersWeight = $childOfParentOrdersWeight + ($orderItem->quantity * $xweight);
                }
            }
        }

        $OldOrderWeight = $OldOrderWeight + $childOfParentOrdersWeight;

        //endforeach

        return $OldOrderWeight;
    }

    public function getNewWeight()
    {
        $customer = \auth()->user();
        //Calculate New Shipping Weight
        $yweight = Setting::getFromName('defualt_product_weight') ? Setting::getFromName('defualt_product_weight') : 120;
        $carts = $customer->carts;
        $newOrderWeight =0;

        //you should give this weight from customer Cart
        foreach ($carts as $cart) {
            if($cart->variety?->weight){
                $yweight = $cart->variety->weight;
            }elseif($cart->variety->product?->weight){
                $yweight = $cart->variety->product->weight;
            }

            $newOrderWeight = $newOrderWeight + ($cart->quantity * $yweight);
        }

        return $newOrderWeight;
    }

    //محاسبه قیمت جدید پست - برای اضافه کردن ایتم به سفارش
    public function getNewShippingPrice($parentOrder,$city)
    {
        $newOrderWeight = 0;


        $shipping = Shipping::find($parentOrder->shipping_id);

        $totalWeight = $this->getOldWeight($parentOrder) + $this->getNewWeight();

        //old shipping amount
        $oldShippingAmount = $shipping->getPrice($city,$this->getOldWeight($parentOrder));

        //new shipping amount
        $newShippingAmount = $shipping->getPrice($city,$totalWeight);

        //Shipping Amount
        //اگر بازه هزینه ارسال تغییر کند،
        // میایم هزینه ارسال قبلی رو از هزینه ارسال جدید کم میکنیم
        // و ما به تفاوت شو از کاربر میگیریم

        $getShippingPrice = $newShippingAmount - $oldShippingAmount;

        return $getShippingPrice;
    }

///////////////////////////////////////////////////////////////////////////
    public static function shipping_amount_calculator_service_from_carts($carts, $customer, $shipping_id, $address_id, bool $wantsError = false)
    {
        /* todo: this is not a good structure */
        $shipping = Shipping::find($shipping_id);
        if (!$shipping) {
            if ($wantsError) throw Helpers::makeValidationException('شیوه ارسال به درستی انتخاب نشده است');
            else return 0;
        }
        $address = Address::query()->where('customer_id', $customer->id)->find($address_id);
        if (!$address) {
            if ($wantsError) throw Helpers::makeValidationException('آدرس اشتباه است');
            else return 0;
        }

        return (new ShippingCalculatorService($address,$shipping, $customer, $carts))->calculate();
        $shippingCalculatorService = new ShippingCalculatorService($address,$shipping, $customer, $carts);
        foreach ($carts as $cart) {
            $shippingCalculatorService->addItem($cart);
        }
        return $shippingCalculatorService->calculate();
//        return $shipping->getPrice($city, Cart::getCartsWeight($carts), $sum_carts, $totalQuantity);
    }







    // came from vendor ================================================================================================
    use HasAuthors, HasCommonRelations, HasFilters,
        SortableTrait, InteractsWithMedia/*, LogsActivity*/;

    protected static $commonRelations = [
        'provinces', 'cities', 'customerRoles'
    ];
    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    protected $appends = ['logo'];


    public static function booted()
    {
        static::deleting(function (\Modules\Shipping\Entities\Shipping $shipping) {
            if ($shipping->orders()->exists()) {
                throw Helpers::makeValidationException('به علت وجود سفارش برای این روش ارسال امکان حذف آن وجود ندارد');
            }
            CacheForgetService::run($shipping);
        });
        static::updating(function (\Modules\Shipping\Entities\Shipping $shipping) {
            CacheForgetService::run($shipping);
        });
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }


//    public function getActivitylogOptions(): LogOptions
//    {
//        $admin = \Auth::user();
//        $name = !is_null($admin->name) ? $admin->name : $admin->username;
//        return LogOptions::defaults()
//            ->useLogName('Shipping')->logAll()->logOnlyDirty()
//            ->setDescriptionForEvent(function ($eventName) use ($name) {
//                $eventName = Helpers::setEventNameForLog($eventName);
//                return "نحوه ارسال {$this->name} توسط ادمین {$name} {$eventName} شد";
//            });
//    }

    //Media library

    public function scopeActive($query)
    {
        $query->where('status', true);
    }

    public function scopeFilters($query)
    {
        return $query;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
    }

    public function addImage($file)
    {
        $media = $this->addMedia($file)
            ->withCustomProperties(['type' => 'shipping'])
            ->toMediaCollection('logo');
        $this->load('media');

        return $media;
    }

    //Custom

    public function getLogoAttribute()
    {
        /* @var $media Media */
        $media = $this->getFirstMedia('logo');
        if (!$media) {
            return null;
        }
        return MediaDisplay::objectCreator($media);
//        return new MediaResource($media);
    }

    public function getPriceByReservation(City $city, int $orderAmount, $newQuantity, $customer, $addressId, int $except = null)
    {
        if ($newQuantity == 0) {
            throw new \LogicException('Total quantity should by not zero');
        }
        $orders = $customer->orders();
        $parentOrder = $orders
            ->where('address_id', $addressId)
            ->where('status', Order::STATUS_RESERVED)
            ->isReserved()
            ->latest()->first();

        $fromCustomer = $this->getForCustomerPrice(Auth::user());
        if ($fromCustomer !== false) {
            return $fromCustomer;
        }

        /** @var $parentOrder Order */
        if ($parentOrder) {
            $shippingPacketPrice = $parentOrder->shipping_packet_amount;
            $oldQuantity = $parentOrder->getTotalTotalQuantity();
            $oldShippingAmountPaid = $parentOrder->shipping_amount;

            return static::getPacketHelper($newQuantity,$this->packet_size,
                $shippingPacketPrice, $this->more_packet_price, $this->first_packet_size, $oldQuantity,$oldShippingAmountPaid);
        }

        $price = $this->getAreaPrice($city, $orderAmount);

        return static::getPacketHelper($newQuantity,$this->packet_size, $price, $this->more_packet_price, $this->first_packet_size);
    }

    public function getAreaPrice($city, $orderAmount)
    {
        $price = $this->attributes['default_price'];
        // برای رزور ها حد آستانه رایگان نداریم
        if (!request('reserved') && $this->free_threshold && $orderAmount >= $this->free_threshold) {
            return 0;
        }elseif ($shippableCity = $this->cities->where('id', $city->id)->first()) {
            $price = $shippableCity->pivot->price;
        } elseif ($shippableProvince = $this->provinces->where('id', $city->province_id)->first()) {
            $price = $shippableProvince->pivot->price;
        }
        return $price;
    }


    // زمانی که برای این نقش مشتری قیمتی تعریف کرده باشیم
    /** @param Customer $customer */
    public function getForCustomerPrice($customer)
    {
        if (!($customer instanceof Customer)) {
            return false;
        }
        $coreSetting = app(CoreSettings::class);
        if (!$coreSetting->get('customer.has_role')) {
            return false;
        }
        /** @var CustomerRole $customerRole */
        $customerRole = $customer->role;
        if (!$customerRole) {
            return false;
        }
        $shipping = $customerRole->shippings()->where('shippings.id', $this->id)->first();
        if ($shipping) {
            return $shipping->pivot->amount;
        }
        return false;
    }

    //Relations

    public function checkShippableAddress(?City $city): bool
    {
        if ($city == null) {
            throw new \Exception('لطفا در آدرس خود یک شهر انتخاب کنید');
        }
        $shippableAddress = false;
        if ($this->cities->count() < 1 && $this->provinces->count() < 1) {
            $shippableAddress = true;
        } elseif ($this->cities->count() > 0) {
            $shippableAddress = $this->cities->contains('id', $city->id);
        } elseif ($this->provinces->count() > 0) {
            $shippableAddress = $this->provinces->contains('id', $city->province_id);
        }

        return $shippableAddress;
    }

    public function customerRoles()
    {
        return $this->belongsToMany(CustomerRole::class)
            ->withPivot(['amount']);
    }

    public function provinces()
    {
        return $this->morphedByMany(Province::class, 'shippable')
            ->active()->withPivot(['price']);
    }

    public function cities()
    {
        return $this->morphedByMany(City::class, 'shippable')
            ->active()->withPivot(['price']);
    }

    /**
     * $oldQuantity برای زمانی هست که رزو کرده یا داره آپدیت میزنه
     * $first_packet_size داره تعیین میکنه بسته ارسالی اول که هزینه شیپینگ رو داره چند تایی باشه
     * $packetSize سایز بسته های بعد از پکت سایز اول
     * $morePrice قیمت هر بسته جدید
     */
    public static function getPacketHelper($quantity, $packetSize, $price, $morePrice,$first_packet_size, $oldQuantity = 0, $oldShippingAmountPaid = 0)
    {
        $allQuantity = $quantity + $oldQuantity;
        if ($allQuantity <= $first_packet_size){
            return $price - $oldShippingAmountPaid;
        }

        $newQuantity = $allQuantity - $first_packet_size;

        $totalPackets = (int)ceil($newQuantity / $packetSize);

        return (int)($price + ($totalPackets) * $morePrice) - $oldShippingAmountPaid;
    }

    public function setProvinces($request)
    {
        $provinces = [];
        foreach ($request->provinces ?? [] as $province) {
            $provinces[$province['id']] = [
                'price' => $province['price'] ?? null
            ];
        }

        $this->provinces()->sync($provinces);
    }

    public function setCustomerRoles($request)
    {
        $customerRoles = [];
        foreach ($request->customer_roles ?? [] as $customerRole) {
            $customerRoles[$customerRole['id']] = [
                'amount' => $customerRole['amount'] ?? null
            ];
        }

        $this->customerRoles()->sync($customerRoles);
    }

    public function isPublic(): bool
	{
		return $this->provinces->isEmpty() &&  $this->cities->isEmpty();
	}
}
