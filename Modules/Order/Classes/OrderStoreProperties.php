<?php

namespace Modules\Order\Classes;

//use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection;
use Modules\Cart\Entities\Cart;
use Modules\Coupon\Entities\Coupon;
use Modules\Customer\Entities\Address;
use Modules\Customer\Entities\Customer;
use Modules\Shipping\Entities\Shipping;
use phpDocumentor\Reflection\Types\Integer;
use Shetabit\Shopit\Modules\Order\Classes\OrderStoreProperties as BaseOrderStoreProperties;

class OrderStoreProperties /*extends BaseOrderStoreProperties*/
{
    public Shipping $shipping;
    public Address $address;
    public ?Coupon $coupon = null;
    public Cart|array|Collection $carts;
    public string|Integer $discount_amount;
    public string|Integer $shipping_amount;
    public string|Integer $shipping_packet_amount;
    public Customer $customer;
    public array $carts_showcase;
    public int $orderWeight;
}
