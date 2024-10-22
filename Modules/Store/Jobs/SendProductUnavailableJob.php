<?php

namespace Modules\Store\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kutia\Larafirebase\Messages\FirebaseMessage;
use Modules\Admin\Entities\Admin;
use Modules\Product\Entities\Variety;
use Modules\Setting\Entities\Setting;
use Modules\Store\Entities\Store;
use Shetabit\Shopit\Modules\Core\Classes\CoreSettings;
use Shetabit\Shopit\Modules\Sms\Sms;


class SendProductUnavailableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $admins;
    private array $tokens;
    private $product;
    private $balance;
    private $variety;

    public function __construct($product,$balance,$variety)
    {
        $this->product = $product;
        $this->balance = $balance;
        $this->variety = $variety;
    }


    public function handle()
    {
        $this->admins = Admin::query();

        $this->firebase($this->product,$this->balance,$this->variety);
        $this->database($this->product,$this->balance,$this->variety);
        $this->sms($this->product,$this->balance,$this->variety);
    }

    public function firebase($product,$balance,$variety)
    {
        $adminIds = $this->getAdminByPermissions(['id'],['write_product', 'store_product'],'id');

        $tokens = DB::table('personal_access_tokens')
            ->where('tokenable_type', 'Modules\Admin\Entities\Admin')
            ->whereNotNull('device_token')
            ->whereIn('tokenable_id', $adminIds)
            ->get('device_token')->pluck('device_token')->toArray();

        if (empty($tokens)) {
            return;
        }

        $mainImage = $product->main_image;
        $message =  (new FirebaseMessage())
            ->withTitle('موجودی رو به اتمام است')
            ->withBody("از محصول {$product->title} با شناسه {$product->id} تعداد {$balance} عدد باقی مانده است. ")
            ->withClickAction('product/' . $product->id);
        if ($mainImage) {
            $message->withImage($mainImage->getFullUrl());
        }
        $message->asNotification(array_values(array_unique($tokens)));
    }

    protected function database($product,$balance,$variety)
    {
        $adminIds = $this->getAdminByPermissions(['id'],['write_product', 'store_product'],'id');
        foreach ($adminIds as $id) {
            DatabaseNotification::query()->create([
                'id' => \Str::uuid(),
                'type' => 'SendProductUnavailableJob',
                'notifiable_type' => 'Modules\Admin\Entities\Admin',
                'notifiable_id' =>  $id,
                'data' =>  [
                    'product_id' => $product->id,
                    'description' => "از محصول {$product->title} با شناسه {$product->id} تعداد {$balance} عدد باقی مانده است. "
                ],
                'read_at' =>  null,
                'created_at' =>  now(),
                'updated_at' =>  now(),
            ]);
        }
    }


    public function sms($product,$balance,$variety)
    {
        //send SMS
        if (!app(CoreSettings::class)->get('sms.patterns.product_unavailable_admin', false)) {
            return;
        }

        $pattern = app(CoreSettings::class)->get('sms.patterns.product_unavailable_admin');

        $adminPhone = Setting::getFromName('phone_for_unavailable_notification_for_admin');
        $adminTwoPhone = Setting::getFromName('phone_for_unavailable_notification_for_admin_two');
        $adminThreePhone = Setting::getFromName('phone_for_unavailable_notification_for_admin_three');

        $variety_name = DB::table('attribute_variety')
            ->where('variety_id',$variety->id)
            ->first();//value


        if (isset($variety_name)) {
            $var = $variety_name->value;
        }elseif (isset($variety->color)) {
            $var = $variety->color->name;
        }

        $name = isset($var) ? $variety->product->title.'|'.$var : $variety->product->title;

        Sms::pattern($pattern)->data([
            'product' => str_replace(' ', '_', $name),
            'balance' => $balance,
        ])->to([$adminPhone])->send();

        Sms::pattern($pattern)->data([
            'product' => str_replace(' ', '_', $name),
            'balance' => $balance,
        ])->to([$adminTwoPhone])->send();

        Sms::pattern($pattern)->data([
            'product' => str_replace(' ', '_', $name),
            'balance' => $balance,
        ])->to([$adminThreePhone])->send();
    }

    public function getAdminByPermissions(array $attribute, array $permissions, $extractAttribute): array
    {
        /** @var Admin $admins */
        $admins = $this->admins
            ->get($attribute);
        $attribute = [];
        /** @var Admin $admin */
        foreach ($admins as $admin) {
            if($admin->hasAnyPermission($permissions) || $admin->hasRole('super_admin')){
                $attribute[] = $admin->$extractAttribute;
            }
        }

        return $attribute;
    }
}
