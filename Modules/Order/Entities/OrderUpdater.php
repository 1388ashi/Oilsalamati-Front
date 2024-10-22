<?php

namespace Modules\Order\Entities;

use Modules\Core\Helpers\Helpers;
use Modules\Customer\Entities\Address;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Modules\Cart\Entities\Cart;
use Modules\Customer\Entities\Customer;
use Modules\Invoice\Classes\Payable;
use Modules\Invoice\Entities\Invoice;
use Modules\Order\Services\Order\OrderUpdaterService;
use Illuminate\Support\Str;
use Modules\Shipping\Entities\Shipping;

class OrderUpdater extends Payable
{
    protected $appends = ['link', 'items'];
    protected $hidden = ['updated_at', 'unique_code', 'update_items'];


    /* todo: we should create some limitation for create a OrderUpdater for each customers. also create a CronJob to kill expired fields */
    const AddItems = 'add_items';
    const DeleteItems = 'delete_items';
//    const EditItemEditQuantity = 'edit_item_edit_quantity';
//    const EditItemUpdateStatus = 'edit_item_update_status';
    const UpdateOrderAddress = 'update_order_address';
    const UpdateShipping = 'update_shipping';


    protected $fillable = ['customer_id','order_id','update_type','update_items','is_done', 'unique_code','payable_amount'];


    public static function store($payable_amount, $customer_id, $order_id, $update_type, $update_items)
    {
        do {
            $unique_code = preg_replace('/[0-9]/', 'p', strtolower(Str::random(10)));
        } while (OrderUpdater::query()->where('unique_code', $unique_code)->count() != 0);


        $newOrderUpdater = OrderUpdater::create([
            'payable_amount' => $payable_amount,
            'customer_id' => $customer_id,
            'order_id' => $order_id,
            'update_type' => $update_type,
            'update_items' => $update_items,
            'is_done' => false,
            'unique_code' => $unique_code,
        ]);
        return $newOrderUpdater;
    }




    public function customer() {
        return $this->belongsTo(Customer::class,'customer_id');
    }
    public function order() {
        return $this->belongsTo(Order::class,'order_id');
    }

    public function isPayable() {
        return !$this->is_done;
    }

    public function getPayableAmount() {
        return $this->payable_amount;
    }


    public function onSuccessPayment(Invoice $invoice)
    {
        // deposit to the customer's wallet,
        $this->customer->deposit($this->getPayableAmount(), [
            'description' => "شارژ کیف پول بابت ویرایش سفارش با شناسه " . $this->order_id
        ]);
        $this->serviceCaller(); // call OrderUpdaterService.
        $this->is_done = true;
        $this->save();
        $this->customer->carts()->delete();
        return $this->callBackViewPayment($invoice);
    }

    public function onFailedPayment(Invoice $invoice): View|Factory|JsonResponse|Application
    {
        $invoice->status = Invoice::STATUS_FAILED;
        $invoice->save();
        return $this->callBackViewPayment($invoice);
    }

    private function callBackViewPayment($invoice)
    {
        return (\Illuminate\Support\Facades\View::exists('basecore::invoice.callback')) ?
            view('basecore::invoice.callback', ['invoice' => $invoice, 'type' => 'order'])
            :
            view('core::invoice.callback', ['invoice' => $invoice, 'type' => 'order']);
    }

    public function link_generator() {
        return env('APP_URL_FRONT') . '/orderUpdater/pay/' . $this->unique_code;
    }

    // SCOPES ==========================================================================================================
    public function scopePayable($query)
    {
        return $query->where('is_done', '=', false)->where('expires_at', '>', now());
    }
    public function scopeActive($query) {
        return $query->where(function($query) {
            $query->where('is_done', 1)
                ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeDone($query) {
        return $query->where('is_done', '=', true);
    }
    // =================================================================================================================
    private function serviceCaller()
    {
        $orderUpdaterService = new OrderUpdaterService($this->order, 'wallet');
        [$addCarts,$deleteCarts,$newAddress,$newShipping] = $this->update_items_loader();

        $orderUpdaterService->validator($addCarts,$deleteCarts,$newAddress,$newShipping);
        return  $orderUpdaterService->applier($addCarts,$deleteCarts,$newAddress,$newShipping);
    }


    public static function CartsSummarizer($carts)
    {
        $newCarts = [];
        foreach ($carts as $cart) {
            $newCarts[] = [
                'variety_id' => $cart->variety_id,
                'quantity' => $cart->quantity,
                'discount_price' => $cart->discount_price,
                'price' => $cart->price,
            ];
        }
        return $newCarts;
    }

    public static function CartsDeSummarizer($carts)
    {
        $newCarts = [];
        foreach ($carts as $cart) {
            $newCart = new Cart([
                'variety_id' => $cart->variety_id,
                'quantity' => $cart->quantity,
                'discount_price' => $cart->discount_price,
                'price' => $cart->price,
            ]);
            $newCart->load(['variety' => function ($query) {$query->with('product');}]); /* todo: because of DontAppend method in final_price method in Variety, we are have to load product to have final_price attribute here. */
            $newCarts[] = $newCart;
        }
        return collect($newCarts);
    }



    public function getLinkAttribute() {
        return $this->link_generator();
    }
    public function getItemsAttribute()
    {
        return $this->update_items_loader();
    }
    public function update_items_loader()
    {
        $updateItems = json_decode($this->update_items);

        $addCarts = OrderUpdater::CartsDeSummarizer($updateItems->addCarts);

        $deleteCarts = OrderUpdater::CartsDeSummarizer($updateItems->deleteCarts);
        $newAddress = ($updateItems->newAddress_id) ? Address::findOrFail($updateItems->newAddress_id) : null;
        $newShipping = ($updateItems->newShipping_id) ? Shipping::findOrFail($updateItems->newShipping_id) : null;

        return [$addCarts,$deleteCarts,$newAddress,$newShipping];


        switch ($this->update_type)
        {
            case self::DeleteItems:
            case self::AddItems:
                $carts = json_decode($this->update_items)->carts;
                return OrderUpdater::CartsDeSummarizer($carts);
            case self::UpdateOrderAddress:
                $new_address_id = json_decode($this->update_items)->new_address_id;
                return Address::findOrFail($new_address_id);
            case self::UpdateShipping:
                $new_shipping_id = json_decode($this->update_items)->new_shipping_id;
                return Shipping::findOrFail($new_shipping_id);
            default:
                throw Helpers::makeValidationException('مشکلی در سیستم رخ داد');
        }
    }



}
