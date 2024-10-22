<?php
require base_path('vendor/shetabit/shopit/src/Modules/Order/Routes/api.php');

use Modules\Order\Http\Controllers\Customer\OrderController as CustomerOrderController;
use Modules\Order\Http\Controllers\Admin\OrderController as AdminOrderController;
use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\OrderUpdaterServiceController;
use Faker\Factory as Faker;
use Modules\Order\Http\Controllers\OrderUpdaterServiceShowcaseController;
use Modules\Order\Http\Controllers\OrderUpdaterController;

Route::superGroup('admin', function () {
    Route::name('orders.create')->get('orders/create', 'OrderController@create');
    Route::put('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->hasPermission('modify_order');
    //todo: return order
//    Route::put('orders/{order}/return', [AdminOrderController::class, 'returnOrder'])
//        ->hasPermission('modify_order');

    Route::get('mini_orders/customer-search', 'MiniOrderController@searchCustomer');
    Route::get('mini_orders/search', 'MiniOrderController@search');
    Route::name('mini_orders.destroy')->delete('mini_orders/{mini_order}','MiniOrderController@destroy')->hasPermission('delete_mini_order');
    Route::name('mini_orders.store')->post('mini_orders','MiniOrderController@store')->hasPermission('mini_order');
    Route::name('mini_orders.index')->get('mini_orders','MiniOrderController@index')->hasPermission('read_mini_order');
    Route::name('mini_orders.show')->get('mini_orders/{mini_order}','MiniOrderController@show')->hasPermission('read_mini_order');

    Route::permissionResource('orders','OrderController');
    Route::post('order/byId', 'OrderController@searchById')->hasPermission('read_order');
    Route::post('orders/{order}/items', 'OrderController@addItem')->hasPermission('write_order');
    Route::put('orders/items/{order_item}', 'OrderController@updateQuantityItem')->hasPermission('modify_order');
    Route::put('orders/items/{order_item}/status', 'OrderController@updateItemStatus')->hasPermission('modify_order');
    Route::post('orders/print/details', 'OrderController@detailsOrderForPrint')->hasPermission('read_order');
    Route::post('orders/status/changes', 'OrderController@changeStatusSelectedOrders')->hasPermission('modify_order');
    Route::delete('shipping_excels/multiple-delete', 'ShippingExcelController@multipleDelete')->name('shipping_excels.multiple-delete');
    Route::get('new_orders/all',[AdminOrderController::class,'allNewOrders'])->hasPermission('write_order');
    Route::get('new_orders',[AdminOrderController::class,'todayOrders'])->hasPermission('write_order');


    // light version
//    Route::get('orders_light',[AdminOrderController::class,'indexLight'])->hasPermission('read_order');
    Route::get('new_orders_light',[AdminOrderController::class,'todayOrdersLight'])->hasPermission('read_order');


    Route::permissionResource('sellers', 'SellerController');
    Route::resource('order-gift-ranges', 'OrderGiftRangeController');

    Route::name('shipping_excels.index')->get('shipping_excels', 'ShippingExcelController@index');
    Route::name('shipping_excels.store')->post('shipping_excels', 'ShippingExcelController@store');
    Route::name('shipping_excels.destroy')->delete('shipping_excels/{shipping_excel}', 'ShippingExcelController@destroy');


    // orderUpdaterService routes ========================
    Route::put('orderUpdater/{order_id}', [OrderUpdaterServiceController::class, 'applier'])->name('orderUpdater.applier');
    Route::post('orderUpdater/{order_id}/showcase', [OrderUpdaterServiceController::class, 'showcase'])->name('orderUpdater.showcase');

    Route::get('/customers/{customer_id}/orderUpdaters', [OrderUpdaterController::class, 'index'])->name('orderUpdater.index');
    Route::delete('/customers/{customer_id}/orderUpdaters/{id}', [OrderUpdaterController::class, 'destroy'])->name('orderUpdater.destroy');
});

Route::superGroup('customer', function () {
    Route::post('orders/newUserOrders',[CustomerOrderController::class, 'newUserOrders']);
    Route::post('orders/addItemShippingAmountForFront',[CustomerOrderController::class, 'addItemShippingAmountForFront']);
    Route::post('orders/{order}/cancelOrder', [CustomerOrderController::class, 'cancelOrder']);
    Route::delete('orders/{order}/items',[CustomerOrderController::class, 'deleteItem']);
    Route::put('/orders/{order}/updateAddress', [CustomerOrderController::class, 'updateAddress'])->name('updateAddress');
    Route::get('userAddresses', [CustomerOrderController::class, 'userAddresses']);

    Route::get('order-gift-ranges',[\Modules\Order\Http\Controllers\Customer\OrderGiftRangeController::class,'index'])->hasPermission('write_order');

    // orderUpdaterService routes
    Route::put('orders/{order_id}/items/updateStatus',[CustomerOrderController::class, 'updateItemStatus'])->name('orders.items.status.updateStatus');


    // orderUpdaterService routes ========================
    Route::put('orderUpdater/{order_id}', [OrderUpdaterServiceController::class, 'applier'])->name('orderUpdater.applier');
    Route::post('orderUpdater/{order_id}/showcase', [OrderUpdaterServiceController::class, 'showcase'])->name('orderUpdater.showcase');

    Route::get('/orderUpdaters', [OrderUpdaterController::class, 'index'])->name('orderUpdater.index');
    Route::delete('/orderUpdaters/{id}', [OrderUpdaterController::class, 'destroy'])->name('orderUpdater.destroy');
    Route::post('/orderUpdaters/{id}/pay', [OrderUpdaterController::class, 'pay'])->name('orderUpdater.pay');


    // came from vendor ================================================================================================
    Route::get('orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/create', [CustomerOrderController::class, 'create'])->name('orders.create');
    Route::get('orders/{order}', [CustomerOrderController::class, 'show'])->where('order','[0-9]+')->name('orders.show');
    Route::post('orders', [CustomerOrderController::class, 'store'])->name('orders.store');
    // ===========================================================================
});


Route::superGroup('front',function () {
    Route::name('shipping_excels.index')->get('shipping_excels', 'ShippingExcelController@index');
}, []);




Route::get('getSystemTime',function (){
    $time = date('H:i:s');
    $allowed_time = '23:45:00';
    $last_day_time = '24:00:00';
    $allowed = $time<$allowed_time;

    $datetime1 = new DateTime($time);
    $datetime2 = new DateTime($last_day_time);
    $interval = $datetime1->diff($datetime2);
//    $diff = $interval->format('%H:%I:%S');
    $diff = $interval->format('%I');

    $data = [
        'time' => $time,
//        'message' => $allowed?'انتقال به درگاه مجاز است':"برای ثبت نهایی خرید، لطفاً $diff دقیقه دیگر اقدام نمایید",
        'message' => $allowed?'انتقال به درگاه مجاز است':"به دلیل اختلالات بانکی بعد از ساعت 12 لطفاً اقدام نمایید",
        'result' => $allowed
    ];
    return response()->success('اجازه برای انتقال به درگاه پراخت',compact('data'));
});




