<?php

namespace Modules\Prize\Http\Controllers\Admin;

//use Shetabit\Shopit\Modules\Prize\Http\Controllers\Admin\GroupChargeController as BaseGroupChargeController;

use Modules\Core\Http\Controllers\BaseController;
use Modules\Prize\Entities\GroupCharge;
use Modules\Prize\Entities\Prize;
use Modules\Prize\Http\Requests\StorePrizeRequest;
use Modules\Report\Entities\OrderReport;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Notifications\DepositWalletSuccessfulNotification;
use Modules\Order\Entities\Order;
use Modules\Order\Services\Statuses\ChangeStatus;

class GroupChargeController extends BaseController
{
    public function index()
    {
        $groupCharges = GroupCharge::query()->paginateOrAll();

        return response()->success('', compact('groupCharges'));
    }

    public function store(StorePrizeRequest $request)
    {
        ini_set('max_execution_time', '300');
        $orderReports = OrderReport::query()
            ->whereIn('status', ChangeStatus::SUCCESS_STATUS)
            ->whereBetween('created_at', [$request->start_date, $request->end_date])
            ->groupBy('customer_id')
            ->havingRaw('SUM(total) > ' . $request->input('amount'));
        $customers = Customer::query()->whereIn('id', $orderReports->pluck('id')->toArray())
            ->get();
        $prizeAmount = $request->input('prize_amount');
        /** @var Customer $customer */
        $groupCharge = new GroupCharge($request->validated());
        $groupCharge->save();
        foreach ($customers as $customer) {
            $customer->deposit($prizeAmount);
            Prize::storeModel($groupCharge, $customer, $prizeAmount);
            $customer->notify(new DepositWalletSuccessfulNotification($customer, $prizeAmount));
        }

        return response()->success('عملیات با موفقیت انجام شد');
    }
}
