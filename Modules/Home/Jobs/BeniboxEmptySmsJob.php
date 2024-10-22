<?php

namespace Modules\Home\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Modules\Product\Entities\Product;
use Modules\Setting\Entities\Setting;
use Shetabit\Shopit\Modules\Core\Classes\CoreSettings;
use Shetabit\Shopit\Modules\Sms\Sms;

class BeniboxEmptySmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    //benibox-empty-sms-job
    public function __construct()
    {
        #TODO
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('BeniboxEmptySmsJob started');
        $mostTimedDiscounts = Product::query()->withCommonRelations()
            ->where('discount','>',0)
            ->where('is_benibox',1)
            ->where('discount_until','>',date("Y-m-d H:i:s"))
            ->whereRaw('discount_until < DATE_ADD(NOW(), INTERVAL 7 DAY)')
            ->get();


        if ($mostTimedDiscounts->count()){
            foreach ($mostTimedDiscounts as $mostTimedDiscount){
                if (
                    $mostTimedDiscount->discount_until > now()->addMinutes(28) &&
                    $mostTimedDiscount->discount_until < now()->addMinutes(30)
                ){
                    //sms
                    $coreSettings = app(CoreSettings::class);
                    $pattern = $coreSettings->get('sms.patterns.benibox_empty_sms_job');
                    $mobile = Setting::getFromName('benibox_empty_sms_job_mobile') ?? '09910407704';
                    $token =str_replace(' ', '-', $mostTimedDiscount->title);
                    Sms::pattern($pattern)->data([
                        'token'=> $token
                    ])->to([$mobile])->send();
                }
            }
        }

    }
}
