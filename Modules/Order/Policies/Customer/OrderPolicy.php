<?php

namespace Modules\Order\Policies\Customer;

//use Shetabit\Shopit\Modules\Order\Policies\Customer\OrderPolicy as BaseOrderPolicy;


use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Customer\Entities\Customer;
use Modules\Order\Entities\Order;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function view(Customer $customer, Order $order)
    {
        return $customer->id === $order->customer_id;
    }
}
