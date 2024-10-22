<?php

namespace Modules\Invoice\Listeners;

//use Shetabit\Shopit\Modules\Invoice\Listeners\CheckStoreOnVerified as BaseCheckStoreOnVerified;

use http\Exception\InvalidArgumentException;
use Modules\Core\Entities\BaseModel;
use Modules\Invoice\Events\GoingToVerifyPayment;
use Modules\Invoice\Events\Modules\Invoice\Events\PymentVerified;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Invoice\Events\PaymentVerified;
use Modules\Invoice\Exceptions\VarietyNotFoundException;
use Modules\Invoice\Exceptions\VarietyQuantityException;
use Modules\Order\Entities\Order;
use Modules\Store\Entities\Store;
use PHPUnit\Framework\Constraint\ExceptionMessage;
use function Sodium\increment;

class CheckStoreOnVerified
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        public Store $store
    ){}

    /**
     * Handle the event.
     *
     * @param GoingToVerifyPayment $event
     * @return void
     * @throws \Exception
     */
    public function handle(GoingToVerifyPayment $event)
    {
        $order = $event->payment->invoice->payable;
        if (!($order instanceof Order)) {
            return;
        }
        $order_items = $order->items;
        foreach ($order_items as $item) {
            $variety = $item->variety;
            if (!$variety) {
                throw new VarietyNotFoundException('محصول انتخاب شده موجودی آن به اتمام رسید');
            }
            if ($variety->quantity < $item->quantity){
                throw new VarietyQuantityException('تعداد سفارش شما بیشتر از موجودی محصول می باشد');
            }
        }
    }
}
