<?php

namespace Modules\Product\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Kutia\Larafirebase\Messages\FirebaseMessage;
use Modules\Customer\Entities\Customer;
use Modules\Product\Emails\ListenDiscountMail;
use Modules\Product\Entities\ListenDiscount;
use Modules\Product\Entities\Product;
use Shetabit\Shopit\Modules\Core\Classes\CoreSettings;
use Shetabit\Shopit\Modules\Sms\Sms;
use Dotenv\Util\Str;

class SendProductDiscountNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $customerIds;
    public Product $product;
    /**
     * @var \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    private array|\Illuminate\Database\Eloquent\Collection $customerPhones;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($product)
    {
        $customerIds = ListenDiscount::query()->where('product_id', $product->id)
            ->get('customer_id')->pluck('customer_id')->toArray();

        $this->customerIds = $customerIds;
        $this->product = $product;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $tokens = DB::table('personal_access_tokens')
            ->where('tokenable_type', 'Modules\Customer\Entities\Customer')
            ->whereNotNull('device_token')
            ->whereIn('tokenable_id', $this->customerIds)
            ->get('device_token')->pluck('device_token')->toArray();

        $this->firebase($this->product, $tokens);
//        $this->sms($this->product, $this->customerIds);
        $this->mail($this->product, $this->customerIds);
        $this->database($this->product, $this->customerIds);

    }

    public function firebase($product, $tokens)
    {

        if (empty($tokens)) {
            return;
        }
        $mainImage = $product->main_image;
        $message =  (new FirebaseMessage())
            ->withTitle('موجودی جدید')
            ->withBody("محصول {$product->title} پیشنهاد ویژه شده است.")
            ->withClickAction('product/' . $product->id);
        if ($mainImage) {
            $message->withImage($mainImage->getFullUrl());
        }
        $message->asNotification(array_values(array_unique($tokens)));
    }

    public function database($product, $customerIds)
    {
        foreach ($customerIds as $id) {
            DatabaseNotification::query()->create([
                'id' => \Str::uuid(),
                'type' => 'SendProductAvailableNotification',
                'notifiable_type' => 'Modules\Customer\Entities\Customer',
                'notifiable_id' =>  $id,
                'data' => [
                    'product_id' => $product->id,
                    'description' => "مشتری عزیز {$product->title} شما پیشنهاد ویژه شد."
                ],
                'read_at' =>  null,
                'created_at' =>  now(),
                'updated_at' =>  now(),
            ]);
        }
    }

    public function mail($product, $customerIds)
    {

        $emails = Customer::query()->select(['id','email'])
            ->whereIn('id', $customerIds)
            ->whereNotNull('email')
            ->get('email')->pluck('email')->toArray();
        if (empty($emails)){
            return;
        }
        Mail::to($emails)->send(new ListenDiscountMail($product));
    }

    #غیر فعال به درخواست مشتری
//    public function sms($product, $customerIds)
//    {
//        $customerPhones= Customer::query()->whereIn('id',$customerIds )
//            ->get('mobile')->pluck('mobile')->toArray();
//
//        if (!app(CoreSettings::class)->get('sms.patterns.product_discount', false)) {
//            return;
//        }
//        $pattern = app(CoreSettings::class)->get('sms.patterns.product_discount');
//
//        foreach ($customerPhones as $customerPhone){
//            Sms::pattern($pattern)->data([
//                'product' => config('app.front_url').'/product/'.$product->id,
//            ])->to([$customerPhone])->send();
//        }
//    }

}
