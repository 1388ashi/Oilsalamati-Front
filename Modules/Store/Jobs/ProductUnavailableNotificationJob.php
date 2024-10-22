<?php

namespace Modules\Store\Jobs;

//use Shetabit\Shopit\Modules\Store\Jobs\ProductUnavailableNotificationJob as BaseProductUnavailableNotificationJob;

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
use Modules\Customer\Entities\Customer;
use Modules\Product\Emails\ListenChargeMail;

class ProductUnavailableNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $admins;
    private $emails;
    private array $tokens;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public $product)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->admins = Admin::query();

        $this->firebase($this->product);

        $this->mail($this->product);

        $this->database($this->product);

    }

    protected function firebase($product)
    {
        $adminIds = $this->getAdminByPermissions(['id'],['write_product', 'store_product'],'id');

        $tokens = DB::table('personal_access_tokens')
            ->where('tokenable_type', 'Modules\Admin\Entities\Admin')
            ->whereNotNull('device_token')
            ->whereIn('tokenable_id', $adminIds)
            ->get('device_token')->pluck('device_token')->toArray();

        $mainImage = $product->main_image;
        if (empty($tokens)) {
            return;
        }
        $message =  (new FirebaseMessage())
            ->withTitle('موجودی ناموجود شد.')
            ->withBody("محصول {$product->title} با شناسه {$product->id} ناموجود شده است.")
            ->withClickAction('product/' . $product->id);
        if (!empty($mainImage)) {
            $message->withImage($mainImage->getUrl());
        }
        $message->asNotification(array_unique($tokens));
    }

    protected function mail($product)
    {
        $emails = $this->getAdminByPermissions(['id', 'email'],['write_product', 'store_product'],'email');
        if (empty($emails)){
            return;
        }
        try {
            \Mail::to($emails)->send(new \Shetabit\Shopit\Modules\Product\Emails\ListenChargeMail($product));
        } catch (\Exception $exception) {
            Log::error($exception->getTraceAsString());
        }
    }

    protected function database($product)
    {

        $adminIds = $this->getAdminByPermissions(['id'],['write_product', 'store_product'],'id');

        foreach ($adminIds as $id) {
            DatabaseNotification::query()->create([
                'id' => \Str::uuid(),
                'type' => 'ProductUnavailableNotification',
                'notifiable_type' => 'Modules\Admin\Entities\Admin',
                'notifiable_id' =>  $id,
                'data' =>  [
                    'product_id' => $product->id,
                    'description' => "محصول {$product->title} با شناسه {$product->id} ناموجود شده است."
                ],
                'read_at' =>  null,
                'created_at' =>  now(),
                'updated_at' =>  now(),
            ]);
        }
    }

    public function getAdminByPermissions(array $attribute, array $permissions, $extractAttribute): array
    {
        /** @var Admin $admins */
        $admins = $this->admins->whereNotNull('email')
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
