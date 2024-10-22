<?php

namespace Modules\Customer\Http\Controllers\Customer;

//use Shetabit\Shopit\Modules\Customer\Http\Controllers\Customer\WithdrawController as BaseWithdrawController;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Customer\Entities\Withdraw;
use Modules\Customer\Http\Requests\Customer\WithdrawCancelRequest;
use Modules\Customer\Http\Requests\Customer\WithdrawStoreRequest;

class WithdrawController extends BaseController
{
    public function index()
    {
        $customer = Auth::user();
        $withdraws = Withdraw::latest()
            ->where('customer_id', $customer->id)->filters()->paginate(20);

        return response()->success('', compact('withdraws'));
    }

    public function show(Withdraw $withdraw)
    {
        $customer = Auth::user();
        $withdraw->where('customer_id', $customer->id);

        return response()->success('', compact('withdraw'));
    }

    public function cancel($id, WithdrawCancelRequest $request)
    {
        $customer = Auth::user();
        $withdraw = Withdraw::where('customer_id', $customer->id)->findOrFail($id);

        try {
            DB::beginTransaction();
            $withdraw->status = Withdraw::STATUS_CANCELED;
            $withdraw->save();
            DB::commit();
            $withdraws = $this->index()->original['data']['withdraws'];

        } catch (\Throwable $throwable) {
            DB::rollBack();
            Log::error($throwable->getTraceAsString());

            return response()->error('مشکلی رخ داد. ', $throwable->getTrace());
        }

        return response()->success('با موفقیت لغو شد', compact('withdraw', 'withdraws'));
    }

    public function store(WithdrawStoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $withdraw = Withdraw::store(Auth::user(), $request);
            $withdraws = $this->index()->original['data']['withdraws'];

            DB::commit();
        } catch (\Throwable $throwable) {
            DB::rollBack();
            Log::error($throwable->getTraceAsString());

            return response()->error('مشکلی رخ داد. ' . $throwable->getMessage(), $throwable->getTrace());
        }

        return response()->success('درخواست برداشت از حساب با موفقیت انجام شد', compact('withdraws', 'withdraw'));
    }
}
