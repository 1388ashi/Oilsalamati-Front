<?php

namespace Modules\Customer\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Admin\Classes\ActivityLogHelper;
use Modules\Area\Entities\City;
use Modules\Area\Entities\Province;
use Modules\Core\Classes\Transaction;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\SmsToken;
use Modules\Customer\Http\Requests\Admin\CustomerDepositRequest;
use Modules\Customer\Http\Requests\Customer\CustomerStoreRequest;
use Modules\Customer\Http\Requests\Customer\CustomerUpdateRequest;
use Modules\Customer\Notifications\DepositWalletSuccessfulNotification;

class CustomerController extends Controller
{
  public function index()
  {
    $customers = Customer::query()
      ->filters()
      ->latest('id')
      ->paginate();

    if (request()->header('Accept') == 'application/json') {
      return response()->success('دریافت لیست همه مشتری ها', compact('customers'));
    }

    return view('customer::admin.customer.index', compact('customers'));
  }

  public function create()
  {
    return view('customer::admin.customer.create');
  }

  public function show($id)
  {
    $customer = Customer::query()->findOrFail($id);
    $balance_gift = $customer->getGiftBalanceAttributes($id);
    
    if (request()->header('Accept') == 'application/json') {
      return response()->success('دریافت جزئیات مشتری', [$customer, 'balance_gift' => $balance_gift]);
    }
    
    $provinces = Province::query()->select('id', 'name')->active()->get();
    $cities = City::select('id', 'name', 'province_id')->get();
    $transactions = $customer->wallet->transactions;

    return view('customer::admin.customer.show', compact(['customer', 'balance_gift', 'transactions', 'cities', 'provinces']));
  }

  public function depositCustomerWallet(CustomerDepositRequest $request)
  {
    $customer = Customer::query()->whereKey($request->customer_id)->first();
    $full_name = $customer->first_name . ' ' . $customer->last_name;
    $customer->deposit($request->amount, [
      'admin_id' => auth()->user()->id,
      'tracking_code' => $request->tracking_code,
      'description' => $request->description ?? " افزایش موجودی حساب توسط ادمین با شناسه " . auth()->user()->id
    ]);
    ActivityLogHelper::updatedModel('افزایش موجودی کیف پول', $customer);

    $customer->notify(new DepositWalletSuccessfulNotification($customer, $request->amount));

    if (request()->header('Accept') == 'application/json') {
      return response()->success("کیف پول مشتری {$full_name} با موفقیت به مبلغ {$request->amount} افزایش یافت");
    }
    return redirect()->route('admin.customers.show', $request->customer_id)
      ->with('success', 'مشتری با موفقیت ایجاد شد.');
  }

  public function transactionsWallet(): \Illuminate\Http\JsonResponse|View
  {
    $transactions = Transaction::query()
      ->where('payable_type', Customer::class)
      ->filters()
      ->latest('id')
      ->with('deposit', fn($q) => $q->select('id', 'amount'))
      ->paginate();

    $totals = $transactions->groupBy('type')->map(fn($items) => $items->sum('amount'));

    $totalThisPage['deposit'] = $totals->get('deposit', 0);
    $totalThisPage['withdraw'] = $totals->get('withdraw', 0);

    if (request()->header('Accept') == 'application/json') {

      $allTotals = Transaction::query()
        ->where('payable_type', Customer::class)
        ->select('type', DB::raw('sum(amount) as total'))
        ->groupBy('type')
        ->pluck('total', 'type');

      $total['deposit'] = $allTotals->get('deposit', 0);
      $total['withdraw'] = $allTotals->get('withdraw', 0);

      return response()->success('گزارشات کیف پول', compact([
        'transactions' => $transactions,
        'total_this_page' => $totalThisPage,
        'total' => $total
      ]));
    }

    return view('customer::admin.wallet.transactions', compact(['transactions', 'totalThisPage']));
  }






  // came from vendor ================================================================================================
  public function search()
  {
    $q = \request('q');
    if (empty($q)) {
      return response()->error('ورودی نامعتبر است');
    }
    $customers = Customer::query()
      ->select('id', 'first_name', 'last_name', 'mobile');
    if (is_numeric($q)) {
      $customers->orWhere('id', $q);
    }
    $customers->orWhere('mobile', 'LIKE', '%' . $q . '%');
    $customers->orWhere('first_name', 'LIKE', '%' . $q . '%');
    $customers->orWhere('first_name', 'LIKE', '%' . explode(' ', $q)[0] . '%');
    $customers->orWhere('last_name', 'LIKE', '%' . $q . '%');
    $customers->orWhere('last_name', 'LIKE', '%' . explode(' ', $q)[0] . '%');

    $count = $customers->count();
    $customers = $customers->take(20)->get();

    return response()->success('', compact('customers', 'count'));
  }

  public function listCustomers()
  {
    $customers = Customer::latest('id')->select('id', 'first_name', 'last_name', 'mobile')->without('wallet')->get();

    return response()->success('', compact('customers'));
  }

  public function store(CustomerStoreRequest $request)
  {
    try {
      DB::beginTransaction();
      $customer = Customer::query()->create($request->all());
      ActivityLogHelper::storeModel('مشتری ایجاد شد', $customer);
      if (!SmsToken::where('mobile', $customer->mobile)->exists()) {
        SmsToken::create([
          'mobile' => $customer->mobile,
          'token' => random_int(10000, 99999),
          'expired_at' => Carbon::now()->addHours(24),
          'verified_at' => now()
        ]);
      }

      DB::commit();
    } catch (\Throwable $throwable) {
      DB::rollBack();
    }

    if (request()->header('Accept') == 'application/json') {
      return response()->success('مشتری با موفقیت ایجاد شد.', compact('customer'));
    }
    return redirect()->route('admin.customers.index')
      ->with('success', 'مشتری با موفقیت ایجاد شد.');
  }
  public function edit(Customer $customer)
  {
    return view('customer::admin.customer.edit', compact('customer'));
  }
  public function update(CustomerUpdateRequest $request, $id)
  {
    $customer = Customer::query()->findOrFail($id);
    $customer->update($request->all());
    ActivityLogHelper::updatedModel('مشتری بروز شد', $customer);
    $customer->loadCommonRelations();
    if (request()->header('Accept') == 'application/json') {
      return response()->success('مشتری با موفقیت بروزرسانی شد.', compact('customer'));
    }
    return redirect()->route('admin.customers.index')
      ->with('success', 'مشتری با موفقیت به روزرسانی شد.');
  }

  public function destroy($id)
  {
    $customer = Customer::query()->findOrFail($id);
    $customer->delete();
    ActivityLogHelper::deletedModel('مشتری حذف شد', $customer);


    if (request()->header('Accept') == 'application/json') {
      return response()->success('مشتری با موفقیت حذف شد.', compact('customer'));
    }
    return redirect()->route('admin.customers.index')
      ->with('success', 'مشتری با موفقیت حذف شد.');
  }


  public function withdrawCustomerWallet(Request $request)
  {
    $request->merge([
      'amount' => str_replace(',', '', $request->input('amount')),
    ]);
    $request->validate([
      'customer_id' => 'required|integer|exists:customers,id',
      'amount' => 'required|integer|min:1000',
      'description' => 'nullable|string'
    ]);
    $admin = auth()->user();
    /** @var Customer $customer */
    $customer = Customer::query()->find($request->customer_id);
    $customer->withdraw($request->amount, [
      'description' => $request->description ?? "کاهش موجودیی کیف پول توسط ادمین با شناسه {$admin->id}"
    ]);

    if (request()->header('Accept') == 'application/json') {
      return response()->success('مبلغ مورد نظر از کیف پول مشتری با موفقیت کسر گردید', compact('customer'));
    }
    return redirect()->route('admin.customers.show', $request->customer_id)->with('success', 'مشتری با موفقیت حذف شد.');
  }
}
