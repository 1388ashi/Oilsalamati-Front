<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Customer\Entities\Customer;
use Modules\CustomersClub\Exports\UserLevelExport;

class CustomersClubScoresReportController extends Controller
{
    public function index()
    {
        $request = \Request();
        $usersQuery = Customer::query()
            ->whereBetween('customers.id',[1,10000])
            ->leftJoin('customers_club_scores', 'customers.id', '=', 'customers_club_scores.customer_id')
            ->when($request->from_date && $request->to_date, function(Builder $q) use ($request) {
                $q->where('customers_club_scores.created_at', '>=', $request->from_date)
                    ->where('customers_club_scores.created_at', '<=', $request->to_date);
            })
            ->when($request->customer_id,function(Builder $q) use ($request){
                $q->where('customers.id', $request->customer_id);
            })
            ->select('customers.*', DB::raw('SUM(customers_club_scores.score_value) as total_score'))
            ->groupBy('customers.id')
            ->orderBy('total_score', 'desc');

        $users = $usersQuery->get();

        $final_list = [];

        foreach ($users as $user){
            $final_list [] = [
                'mobile' => $user->mobile,
                'full_name' => $user->first_name . " " . $user->last_name,
                'level' => $user->customers_club_level['level'],
                'score' => $user->customers_club_score,
                'bon' => $user->customers_club_bon,
            ];
        }

        DB::table('customers_club_scores_report')->insert($final_list);
        dd($final_list);

    }


    public function add()
    {
        Schema::create('customers_club_scores_report', function (Blueprint $table) {


            $table->string('mobile')->nullable();
            $table->string('full_name')->nullable();
            $table->string('level')->nullable();
            $table->string('score')->nullable();
            $table->string('bon')->nullable();

        });
        dd('MIGRATION DONE');
    }


    public function get_excel()
    {
        $list = DB::table('customers_club_scores_report')->get();
        return Excel::download(new UserLevelExport($list),
            __FUNCTION__.'-' . now()->toDateString() . '.xlsx');
    }
}
