<?php

namespace Modules\CustomersClub\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\CustomersClub\Entities\CustomersClubScore;

class RemoveDuplicateScores implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $list_ids = CustomersClubScore::query()
            ->select(
                'id',
                DB::raw('count(*) as count')
            )
            ->groupBy(['customer_id', 'cause_id', 'cause_title', 'date'])
            ->having(DB::raw('count'),'>',1)
            ->pluck('id');

        DB::table('customers_club_scores')
            ->whereIn('id', $list_ids)
            ->delete();

        Log::info('رکوردهای تکراری امتیازات باشگاه مشتریان حذف شد');
    }
}
