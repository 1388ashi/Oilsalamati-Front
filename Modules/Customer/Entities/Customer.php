<?php

namespace Modules\Customer\Entities;

use Bavix\Wallet\Interfaces\Customer as CustomerWallet;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Traits\CanPay;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;
use Modules\Auth\Traits\HasPushTokens;
use Modules\Cart\Entities\Cart;
use Modules\Contact\Entities\Contact;
use Modules\Core\Classes\CoreSettings;
use Modules\Core\Traits\HasAuthors;
use Modules\Core\Traits\InteractsWithMedia;
use Modules\Core\Transformers\MediaResource;
use Modules\Coupon\Entities\Coupon;
use Modules\CustomersClub\Entities\CustomersClubScore;
use Modules\Notification\Entities\Notification;
use Modules\Order\Entities\Order;
use Modules\Order\Services\Order\OrderCreatorService;
use Modules\Product\Entities\ListenCharge;
use Modules\Order\Entities\OrderUpdater;
use Modules\Product\Entities\ListenDiscount;
use Modules\Product\Entities\Product;
use Modules\ProductComment\Entities\ProductComment;
use Modules\Shipping\Entities\Shipping;
use Modules\Core\Entities\BaseModelTrait;
//use Shetabit\Shopit\Modules\Customer\Entities\Customer as BaseCustomer;
use Shetabit\Shopit\Modules\Customer\Entities\SmsToken;
use Spatie\MediaLibrary\HasMedia;

class Customer extends User implements CustomerWallet, \Modules\Core\Contracts\Notifiable, HasMedia
{
    protected $appends = [
        'full_name',
        'image',
        'customers_club_score',
        'customers_club_bon',
        'customers_club_level',
        'invite_link'
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'mobile',
        'national_code',
        'gender',
        'card_number',
        'birth_date',
        'newsletter',
        'foreign_national',
        'invite_code'
    ];

    protected static $commonRelations = [
        /*'addresses', 'favorites','listenDiscounts'*/
    ];

    public function getGiftBalanceAttributes($customer_id)
    {
        return DB::table('wallets')
            ->where('holder_type', 'Modules\Customer\Entities\Customer')
            ->where('holder_id', $customer_id)
            ->latest('id')
            ->first()->gift_balance;
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function listenDiscounts()
    {
        return $this->hasMany(ListenDiscount::class);
    }

    #TODO : Test
    public function getCustomersClubScoreAttribute()
    {
        return (integer)CustomersClubScore::where('customer_id',$this->id)->SearchBetweenTwoDate()->where('status',1)->sum('score_value');
    }

    public function getCustomersClubBonAttribute()
    {
        return (integer)CustomersClubScore::where('customer_id',$this->id)->SearchBetweenTwoDate()->where('status',1)->sum('bon_value');
    }

    public function getCustomersClubLevelAttribute()
    {
        $score = (integer)CustomersClubScore::where('customer_id',$this->id)->SearchBetweenTwoDate()->where('status',1)->sum('score_value');
        // محاسبه سطح مشتری با توجه به امتیاز دریافت شده
        $level = \Modules\CustomersClub\Entities\CustomersClubLevel::query()
            ->where('min_score','<=',$score)
            ->where('max_score','>',$score)
            ->first();

        if (!$level){
            // در صورتی که سطح موردنظر برای کاربر یافت نشود، کمترین سطح برای او درنظر گرفته می شود
            $level = \Modules\CustomersClub\Entities\CustomersClubLevel::query()->orderBy('min_score')->first();
        }

        return [
            'id' => $level->id,
            'level' => $level->title,
            'color' => $level->color,
            'image' => $level->image,
        ];

    }



    public function get_carts_showcase($carts = null)
    {
        return (new OrderCreatorService(
            carts: $carts ?? $this->carts,
            customer: $this,
            address: \request('address_id') ? $this->addresses()->where('id', \request('address_id'))->first() : null,
            shipping: \request('shipping_id') ? Shipping::query()->active()->where('id', \request('shipping_id'))->first(): null,
            coupon: \request('coupon_code') ? Coupon::where('code', \request('coupon_code'))->first() : null,
            discount_on_order: \request('discount_on_order') ?? 0,
            pay_type: \request('pay_type') ?? null,
            payment_driver: \request('payment_driver') ?? null,
        ))->calculator();
    }


    public static function getUniqueInviteCode() : string {
        do {
            $text = preg_replace('/[0-9]/', 'p', strtolower(Str::random(8)));
        } while (Customer::query()->where('invite_code', $text)->count() != 0);
        return $text;
    }

    public function getInviteLinkAttribute() {
        return env('APP_URL_FRONT') . '/invite-link/' . $this->invite_code;
    }


    public function orderUpdaters() {
        return $this->hasmany(OrderUpdater::class, 'customer_id');
    }






    // came from vendor ================================================================================================
    use BaseModelTrait, InteractsWithMedia, HasApiTokens, HasAuthors, CanPay, Notifiable, HasPushTokens/*, LogsActivity*/;

    const MALE = 'male';
    const FEMALE = 'female';

    const NOTIFICATION_FIELDS = ['id', 'read_at', 'type', 'data', 'created_at'];

    protected $with = ['wallet'];



    protected $hidden = [
        'password',
        'remember_token',
        'media',
        'updater'
    ];

    public function __construct(array $attributes = [])
    {
        $coreSetting = app(CoreSettings::class);
        if ($coreSetting->get('customer.has_role')) {
            $this->with = array_merge($this->with, ['role']);
            $this->mergeFillable(['role_id']);
        }
        parent::__construct($attributes);
    }

//    public function getActivitylogOptions(): LogOptions
//    {
//        $user = \Auth::user() ?? $this;
//        $updater = !is_null($user->name) ? $user->name : $user->username;
//        $updated = $this->first_name ?? '' .' '. $this->last_name ?? '';
//        $type = $updater == $updated ? 'خودش را ' : "توسط {$updater}";
//        return LogOptions::defaults()
//            ->useLogName('Customer')
//            ->logAll()
//            ->logOnlyDirty()
//            ->setDescriptionForEvent(function($eventName) use ($updated, $type){
//                $eventName = Helpers::setEventNameForLog($eventName);
//                return "مشتری  {$updated} {$type} {$eventName} شد";
//            });
//    }

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public static function booted()
    {
        static::deleting(function (\Modules\Customer\Entities\Customer $customer) {
          if ($customer->orders()->exists()) {
            return redirect()->route('admin.customers.index')
            ->with('success', 'مشتری به دلیل داشتن سفارش قابل حذف نمی باشد.');
          }
        });
        static::deleted(function (\Modules\Customer\Entities\Customer $customer) {
            $smsToken = \Modules\Customer\Entities\SmsToken::query()->firstWhere('mobile', $customer->mobile);
            $smsToken?->delete();
        });
    }

    public static function getAvailableGenders()
    {
        return [static::MALE, static::FEMALE];
    }

    public function getFullNameAttribute(): string
    {
        if (!$this->first_name && !$this->last_name) {
            return '';
        }
        return $this->first_name . ' ' . $this->last_name;
    }

    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = bcrypt($value);
        } else {
            $this->attributes['password'] = null;
        }
    }
    public function scopeFilters($query)
    {
        return $query
            ->when(request('first_name'), function($query){
                $query->where('first_name', 'like', "%". request('first_name') ."%");
            })
            ->when(request('last_name'), function($query){
                $query->where('last_name', 'like', "%". request('last_name') ."%");
            })
            ->when(request('mobile'), function($query){
                $query->where('mobile', request('mobile'));
            })
            ->when(request('id'), function($query){
                $query->where('id', request('id'));
            })
            ->when(request('has_deposits'), function($query){
                $query->whereHas('deposits', fn($query) => $query->where('status', 'success'));
            })
            ->when(request('has_transactions'), function($query){
                $query->whereHas('transactions');
            })
            ->when(request('start_date'), function($query){
                $query->whereDate('created_at', '>=', request('start_date'));
            })
            ->when(request('end_date'), function($query){
                $query->whereDate('created_at', '<=', request('end_date'));
            })
            ->when(request('city_id') || request('province_id'), function($q) {  
                $q->whereHas('addresses', function($q) {  
                    if (request('city_id')) {  
                        $q->where('city_id', request('city_id'));  
                    }  
                    if (request('province_id')) {  
                        $q->orWhereHas('city', function($q) {  
                            $q->where('province_id', request('province_id'));  
                        });  
                    }  
                });  
            });
    }

    /**
     * @throws ValidationException
     */
    public function verify(string $mobile, string $token)
    {
        $smsToken = SmsToken::where('mobile', $mobile)->first();

        if (!$smsToken || $smsToken->token !== $token) {
            throw ValidationException::withMessages([
                'sms_token' => 'کد وارد شده نادرست است.'
            ]);
        }

        if (Carbon::now()->diffInMinutes($smsToken->updated_at) > 5) {
            throw ValidationException::withMessages([
                'sms_token' => 'کد وارد شده منقضی شده است.'
            ]);
        }

        //update verified_at
        $smsToken->verified_at = now();
        $smsToken->save();
    }

    //Relations

    public function role()
    {
        return $this
            ->belongsTo(
                \Modules\Customer\Entities\CustomerRole::class, 'role_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function productComments(): HasMany
    {
        return $this->hasMany(ProductComment::class, 'creator_id');
    }

    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'favorites')->with('varieties');
    }

    public function listenCharges()
    {
        return $this->hasMany(ListenCharge::class);
    }

    public function deposits()
    {
        return $this->hasMany(\Modules\Customer\Entities\Deposit::class);
    }

    public function smsTokens()
    {
        return $this->hasOne(\Modules\Customer\Entities\SmsToken::class,
            'mobile', 'mobile');
    }

    /**
     * Get the entity's notifications.
     *
     * @return MorphMany
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest('created_at');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->singleFile();
    }

    public function addImage($file)
    {
        if (!$file) return;
        $media = $this->addMediaFromBase64($file)
            ->toMediaCollection('images');
        $this->load('media');

        return new MediaResource($media);
    }

    public function getImageAttribute()
    {
        $media = $this->getFirstMedia('images');
        if (!$media) return;

        return new MediaResource($media);
    }

    public function prepareForExcel()
    {
        $this->makeHidden('image');
    }

    public function canSeeUnpublishedProducts()
    {
        if (!$this->role_id) {
            return false;
        }
        $role = $this->role;

        return $role && $role->see_expired;
    }

    public function removeEmptyCarts()
    {
        $carts = $this->carts()->get();
        foreach ($carts as $cart) {
            if ($cart->quantity == 0) {
                $cart->delete();
            }
        }
        $this->load('carts');
    }

    public function isActive()
    {
        // هیچوقت نال نیست ولی برای ساپورت پروژه های قبلی
        return $this->status === null || $this->status === 1;
    }


    public function getAllWalletTransactionsAttribute()
    {
        if (!$this->wallet) {
            return [
                'wallet_balance' => 0,
                'total_deposit' => 0,
                'total_withdraw' => 0,
                'deposit_count' => 0,
                'withdraw_count' => 0,
            ];
        }
        
        $balance = $this->wallet->balance;

        $totalDeposit = $this->wallet->transactions()  
            ->where('type', 'deposit')  
            ->where('confirmed', true)  
            ->sum('amount');  

        $totalWithdraw = $this->wallet->transactions()  
            ->where('type', 'withdraw')  
            ->where('confirmed', true)  
            ->sum('amount');  

        $depositCount = $this->wallet->transactions()  
            ->where('type', 'deposit')  
            ->where('confirmed', true)  
            ->count();  

        $withdrawCount = $this->wallet->transactions()  
            ->where('type', 'withdraw')  
            ->where('confirmed', true)  
            ->count();  

        return [  
            'wallet_balance' => $balance,  
            'total_deposit' => $totalDeposit,  
            'total_withdraw' => abs($totalWithdraw),  
            'deposit_count' => $depositCount,  
            'withdraw_count' => $withdrawCount,  
        ];  
    }

}
