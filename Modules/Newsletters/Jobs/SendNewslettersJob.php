<?php

namespace Modules\Newsletters\Jobs;

//use Shetabit\Shopit\Modules\Newsletters\Jobs\SendNewslettersJob as BaseSendNewslettersJob;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Mail;
use Modules\Core\Helpers\Helpers;
use Modules\Newsletters\Emails\NewslettersEmail;
use Modules\Newsletters\Entities\Newsletters;
use Modules\Newsletters\Entities\UsersNewsletters;

class SendNewslettersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public  $email,
        public Newsletters $newsletters,
        public  $sendAt
    ){
        $delayByMinutes = (int)Carbon::now()->diffInMinutes($this->sendAt);
        $this->delay(now()->addMinutes($delayByMinutes));
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        $email = new NewslettersEmail($this->newsletters);
        Mail::to($this->email)->send($email);
        return true;
    }
}
