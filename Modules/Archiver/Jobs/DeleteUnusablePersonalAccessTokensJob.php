<?php

namespace Modules\Archiver\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class DeleteUnusablePersonalAccessTokensJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $max_storage_time_per_day = 30;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /* attention: in this job we delete those personal access tokens that their last_used_at are for 2 month before */
        DB::table('personal_access_tokens')->where('last_used_at', "<", Carbon::now()->subDay($this->max_storage_time_per_day))->orWhereNull('last_used_at')->delete();
    }
}
