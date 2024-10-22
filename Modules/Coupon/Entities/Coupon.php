<?php

namespace Modules\Coupon\Entities;

//use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
//use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
//use Illuminate\Validation\Rule;
use Modules\Cart\Entities\Cart;
use Modules\Category\Entities\Category;
//use Modules\Core\Entities\BaseModel;
use Modules\Core\Helpers\Helpers;
use Modules\Core\Traits\HasAuthors;
//use Modules\Coupon\Http\Requests\Admin\CouponStoreRequest;
use Modules\Customer\Entities\Customer;
use Modules\Setting\Entities\Setting;
use Shetabit\Shopit\Modules\Core\Classes\CoreSettings;
//use Shetabit\Shopit\Modules\Coupon\Entities\Coupon as BaseCoupon;
use Illuminate\Support\Str;
use Shetabit\Shopit\Modules\Sms\Sms;
//use Spatie\Activitylog\LogOptions;

class Coupon extends Model
{
    protected $fillable = [
        "title",
        "code",
        "start_date",
        "end_date",
        "type",
        "coupon_type",
        "amount",
        "usage_limit",
        "usage_per_user_limit",
        'min_order_amount'
    ];

    const ORDER_GIFT_COUPON = 'order_gift';
    const ADMIN_COUPON = 'admin';

    public static function getAvailableQCouponType(): array
    {
        return [
            static::ORDER_GIFT_COUPON,
            static::ADMIN_COUPON,
        ];
    }


    public static function getQCouponTypeLabelAttribute($c)
    {
        $t = [
            'order_gift' => 'هدیه خرید',
            'admin' => 'ساخته شده توسط ادمین',
        ];

        return $t[$c];
    }

    public function scopeFilterByType($q)
    {
        $coupon_type =  request('coupon_type');
        if (request()->has('coupon_type')){
            return $q->where('coupon_type',$coupon_type);
        }
    }

    public static function onSuccessPeymentCoupon($orderId,$customerPhone)
    {
        $coupon= Coupon::create([
            'title' => $orderId.'هدیه سفارش شماره ',
            'code' =>Str::random(12),
            'start_date' => now(),
            'end_date' => now()->addDays(30)->endOfDay(),
            'type' => 'percentage',
            'coupon_type' => 'order_gift',
            'amount' => Setting::getFromName('value_of_coupon_success_order') ? : 10,
            'usage_limit' => 1,
            'usage_per_user_limit' => 1,
            'min_order_amount' => 1000,
        ]);

        if ($coupon){
            //send sms
            if (!app(CoreSettings::class)->get('sms.patterns.coupon_for_success_order', false)) {
                return;
            }
            $pattern = app(CoreSettings::class)->get('sms.patterns.coupon_for_success_order');

            Sms::pattern($pattern)->data([
                'token' => $coupon->amount,
                'token2' => $coupon->code
            ])->to([$customerPhone])->send();
        }



    }


    public function categories()
    {
        return $this->belongsToMany(Category::class,
            'coupon_categories',
            'coupon_id',
            'category_id')
            ->withPivot(['amount']);
    }


    // came from vendor ================================================================================================
    use HasAuthors,SoftDeletes;


    public function scopeSearchKeyword($query)
    {
        return $query->when($query,function ($q) {
            return $q->where('title','LIKE','%'.\request('keyword').'%')
                ->orWhere('code','LIKE','%'.\request('keyword').'%')
                ;
        });
    }

//    protected static array $commonRelations = ['customers'];

    CONST DISCOUNT_TYPE_FLAT = 'flat';
    CONST DISCOUNT_TYPE_PERCENTAGE = 'percentage';

//    public function getActivitylogOptions(): LogOptions
//    {
//        $admin = \Auth::user();
//        $name = !is_null($admin->name) ? $admin->name : $admin->username;
//        return LogOptions::defaults()
//            ->useLogName('Coupon')->logAll()->logOnlyDirty()
//            ->setDescriptionForEvent(function($eventName) use ($name){
//                $eventName = Helpers::setEventNameForLog($eventName);
//                return "انبار {$this->title} توسط ادمین {$name} {$eventName} شد";
//            });
//    }

    # Relation Functions
    public function customers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Customer::class)->withTimestamps();
    }
    #End Relations

    #Other Functions
    public static function getAvailableTypes(): array
    {
        return [static::DISCOUNT_TYPE_FLAT, static::DISCOUNT_TYPE_PERCENTAGE];
    }

    /**
     * @param int|\Shetabit\Shopit\Modules\Coupon\Entities\Coupon $coupon
     * @return int
     */
    public function countCouponUsed(int|Coupon $coupon): int
    {
        if ($coupon instanceof Coupon){
            return $coupon->customers()->count();
        }

        return DB::table('coupon_customer')->where('coupon_id' , $coupon)->count();
    }

    /**
     * @param int|Customer $customer
     * @param int $couponId
     * @return int
     */
    public function countCouponUsedByCustomer(int|Customer $customer, int $couponId): int
    {
        if ($customer instanceof Customer){
            return $customer->coupons()->where('coupon_id' , $couponId)->count();
        }

        return DB::table('coupon_customer')->where('customer_id' , $customer)
            ->where('coupon_id' , $couponId)->count();
    }

    public function getTotalUsageAttribute()
    {
        if ($this->customers_count === null) {
            return $this->customers()->count();
        }

        return $this->customers_count;
    }

    public static function useCoupon($customerId, $couponId)
    {
        DB::table('coupon_customer')->insert([
            'customer_id' => $customerId,
            'coupon_id' => $couponId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public static function dontAllowCouponAndDiscountTogether()
    {
        $coreSettings = app(CoreSettings::class);
        if ($coreSettings->get('order.allow_coupon_with_discount')) {
            return;
        } else if (!$coreSettings->get('order.allow_coupon_mixed')) {
            static::allow_coupon_with_discount();
        } else {
            static::allow_coupon_mixed();
        }
    }

    // اجازه میکس نده و اجازه همراه با تخفیف نده
    public static function allow_coupon_with_discount()
    {
        /** @var Customer $customer */
        if ($customer = Auth::user()) {
            $hasDiscount = false;
            /** @var Cart $cart */
            foreach ($customer->carts()->with([
                'variety' => fn($q) => $q->withCommonRelations()
            ])->get() as $cart
            ) {
                if ($cart->variety->final_price['discount_price']) {
                    $hasDiscount = true;
                }
            }

            if ($hasDiscount) {
                throw Helpers::makeValidationException('به علت وجود محصول در جشنوار امکان استفاده از کد تخفیف وجود ندارد');
            }
        }
    }

    // اونایی که تو جشنواره ان تخفیف اعمال نشه - باید حداقل یک محصول بدون تخفیف باشه
    public static function allow_coupon_mixed()
    {
        /** @var Customer $customer */
        if ($customer = Auth::user()) {
            $hasAnyNoDiscount = false;
            /** @var Cart $cart */
            foreach ($customer->carts()->with([
                'variety' => fn($q) => $q->withCommonRelations()
            ])->get() as $cart
            ) {
                if (!$cart->variety->final_price['discount_price']) {
                    $hasAnyNoDiscount = true;
                }
            }

            if (!$hasAnyNoDiscount) {
                throw Helpers::makeValidationException('به علت وجود تمامی محصولات در جشنواره امکان استفاده از کد تخفیف وجود ندارد');
            }
        }
    }
    public function scopeFilters($query)
    {
        return $query
            ->when(request('name'), fn($q) => $q->where('name', 'LIKE', '%' . request('name') . '%'))
            ->when(request('code'), fn($q) => $q->where('code', request('code')))
            ->when(request('start_date'), fn($q) => $q->whereDate('start_date', '>=', request('start_date')))
            ->when(request('end_date'), fn($q) => $q->whereDate('end_date', '<=', request('end_date')));
    }
}
