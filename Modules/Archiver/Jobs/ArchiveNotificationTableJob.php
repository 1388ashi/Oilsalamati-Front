<?php

namespace Modules\Archiver\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Archiver\Entities\ArchiveManager;

class ArchiveNotificationTableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $max_count_of_data_load_ram_limit = 100000;
    private $max_storage_time_per_day = 90;
    private $max_rows_per_query = 300;

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
        // to when variable
        $toWhen = Carbon::now()->subDay($this->max_storage_time_per_day);

        // get tables list that should clear.
        $table = 'notifications';
        // create archive table name
        $table_name = $table . "_" . now()->format('Ymd_His');
        // create archive table with function in archive database
        if (!$this->create_table($table, $table_name)) { return; } // if we couldn't create this table in archive database we shouldn't delete its fields in main database

        $datasCount = DB::connection('mysql')->table($table)->where('created_at', '<', $toWhen)->count();

        while (DB::connection('mysql')->table($table)->where('created_at', '<', $toWhen)->count() != 0)
        {
            $dataToCopy = DB::connection('mysql')->table($table)
                ->where('created_at', '<', $toWhen)
                ->orderBy('created_at', 'desc')
                ->limit($this->max_count_of_data_load_ram_limit)
                ->get();
            // convert collection to array
            $dataToCopyArray = $dataToCopy->map(function ($item) {
                return (array) $item;
            })->toArray();


            $chunkedData = array_chunk($dataToCopyArray, $this->max_rows_per_query);

            foreach ($chunkedData as $saveData)
            {
                // create query . attention:: I want to fill all rows with some queries
                DB::connection('mysql2')->table($table_name)->insert($saveData);
                // delete all these fields from main database
                DB::table($table)
                    ->where('created_at', '<', $toWhen)
                    ->orderBy('created_at', 'desc')
                    ->limit($this->max_count_of_data_load_ram_limit)
                    ->delete();
            }
            // check that all fields copied or not with count method. // if not we should check all of them one by one
//            if ($datasCount != DB::connection('mysql2')->table($table_name)->count())
//            {
//                // todo: I have to do a job for this.
//                dump('the count was not correct');
//            }

        }





        // save transaction information on archive_manager table
        ArchiveManager::create([
            'tablename_source' => $table,
            'tablename_archive' => $table_name,
            'row_count' => $datasCount,
            'id_start' => 0,
            'id_end' => 0,
        ]);

    }

    private function create_table($table_name, $DB_table_name): bool
    {
        $create_table_function = "create_table_" . $table_name;
        if (method_exists($this, $create_table_function)) // we call this table creator if exists
        {
            call_user_func([$this, $create_table_function], $DB_table_name);
            return true;
        }
        return false;

    }


    private function create_table_notifications($table_name): void
    {
        Schema::connection('mysql2')->create($table_name, function (Blueprint $table) {
            $table->char('id', 36);
            $table->string('type');
            $table->string('notifiable_type')->nullable();
            $table->unsignedBigInteger('notifiable_id')->nullable();
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_notified')->nullable();
            $table->char('public_uuid', 36)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }



}
