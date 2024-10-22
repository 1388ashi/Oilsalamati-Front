<?php

namespace Modules\Archiver\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Archiver\Entities\ArchiveManager;

class ArchiveBigTablesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $archiveTables = [
        'activity_log',
//        'failed_jobs',
//        'order_item_logs',
//        'order_logs',
//        'order_status_logs',
//        'webhook_logs'
    ];
    private $max_count = 1000;
    private $max_count_of_data_load_ram_limit = 100000;
    private $max_rows_per_query = 1000;
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
//        $lastTime = now()->getTimestampMs();
//        dump($lastTime);
        // get tables list that should clear.
        $tables = $this->archiveTables;
        $max_count = $this->max_count;
        // foreach loop to get each database
        foreach ($tables as $table)
        {
            // count tables fields if it was bigger than a value, we should send them to the archive database. attention: we do this with just count method.
//            $here_time = now()->getTimestampMs();
//            dump('before count all rows ' . $here_time - $lastTime);
//            $lastTime = $here_time;
            if (DB::table($table)->count() < $max_count)
                { continue; }
//            $here_time = now()->getTimestampMs();
//            dump('after count all rows ' . $here_time - $lastTime);
//            $lastTime = $here_time;
            // create archive table name
            $table_name = $table . "_" . now()->format('Ymd_His');
//            dump($table_name);
            // we count all database fields
            $datasCount = DB::connection('mysql')->table($table)->count();
//            dump($datasCount);

            // we get first and last id of all data that we want to migrate them into the archive database
            $first_id = DB::table('activity_log')->select('id')->orderBy('id', 'desc')->limit(1)->value('id');
            $last_id = DB::table('activity_log')->select('id')->orderBy('id', 'asc')->limit(1)->value('id');
//            $here_time = now()->getTimestampMs();
//            dump('after first and second ' . $here_time - $lastTime);
//            $lastTime = $here_time;
            // create archive table with function in archive database
            if (!$this->create_table($table, $table_name)) { continue; } // if we couldn't create this table in archive database we shouldn't delete its fields in main database

            /* on database of server might to have many big data. so we separate them per max_count.
             * and we do all works separately */
            while (DB::connection('mysql')->table($table)->count() >= $this->max_count)
            {
//                $here_time = now()->getTimestampMs();
//                dump('before read piece of rows ' . $here_time - $lastTime);
//                $lastTime = $here_time;
                // we read piece of database fields to save on archive and delete them from database
                $dataToCopy = DB::connection('mysql')->table($table)
                    ->orderBy('id', 'desc')
                    ->limit($this->max_count_of_data_load_ram_limit)
                    ->get();
//                $here_time = now()->getTimestampMs();
//                dump('after read piece of rows ' . $here_time - $lastTime);
//                $lastTime = $here_time;

                $from = $dataToCopy->max('id');
                $to = $dataToCopy->min('id');
//                dump('from: ' . $from . ' to: ' . $to);

                // convert datas to array for chunk
                $dataToCopyArray = $dataToCopy->map(function ($item) {
                    return (array) $item;
                })->toArray();

                $chunkedData = array_chunk($dataToCopyArray, $this->max_rows_per_query);

                foreach ($chunkedData as $saveData)
                {
//                    $here_time = now()->getTimestampMs();
//                    dump('before insert in chunk ' . $here_time - $lastTime);
//                    $lastTime = $here_time;
                    // create query . attention:: I want to fill all rows with some queries
                    DB::connection('mysql2')->table($table_name)->insert($saveData);
                    // delete all these fields from main database
//                    $here_time = now()->getTimestampMs();
//                    dump('after insert in chunk ' . $here_time - $lastTime);
//                    $lastTime = $here_time;
                    $ids_list = [];
                    foreach ($saveData as $item) { $ids_list[] = $item['id']; }
                    DB::table($table)->whereIn('id', $ids_list)->delete();
//                    $here_time = now()->getTimestampMs();
//                    dump('after delete in chunk ' . $here_time - $lastTime);
//                    $lastTime = $here_time;
                }
                // check that all fields copied or not with count method. // if not we should check all of them one by one
                /*if ($datasCount != DB::connection('mysql2')->table($table_name)->count())
                {
                    // todo: I have to do a job for this.
                    // dump('the count was not correct');
                }*/


//                dd('here');
            }


            // save transaction information on archive_manager table
            ArchiveManager::create([
                'tablename_source' => $table,
                'tablename_archive' => $table_name,
                'row_count' => $datasCount,
                'id_start' => $first_id,
                'id_end' => $last_id,
//                'created_at_start' => ($dataToCopy->where('id', min($ids_list))->first())->created_at,
//                'created_at_end' => ($dataToCopy->where('id', max($ids_list))->first())->created_at,
            ]);

        }

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
    private function create_table_activity_log($table_name): void
    {
        Schema::connection('mysql2')->create($table_name, function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->string('subject_type')->nullable();
            $table->string('event')->nullable();
            $table->unsignedBigInteger('subject_id');
            $table->string('causer_type')->nullable();
            $table->unsignedBigInteger('causer_id');
            $table->longText('properties')->nullable();
            $table->char('batch_uuid', 36)->nullable();
            $table->timestamps();
        });
    }

    private function create_table_failed_jobs($table_name): void
    {
        Schema::connection('mysql2')->create($table_name, function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('uuid');
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at');
        });
    }

    private function create_table_order_item_logs($table_name): void
    {
        Schema::connection('mysql2')->create($table_name, function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('order_item_id');
            $table->unsignedBigInteger('order_log_id');
            $table->enum('type', ['decrement','delete','increment','new']);
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('creator_id');
            $table->unsignedBigInteger('updater_id');
            $table->timestamps();
        });
    }

    private function create_table_order_logs($table_name): void
    {
        Schema::connection('mysql2')->create($table_name, function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('shipping_id')->nullable();
            $table->longText('address')->nullable();
            $table->bigInteger('amount');
            $table->enum('status', ['wait_for_payment','in_progress','delivered','new','canceled','failed','reserved']);
            $table->bigInteger('coupon');
            $table->unsignedBigInteger('creator_id');
            $table->unsignedBigInteger('updater_id');
            $table->timestamps();
        });
    }

    private function create_table_order_status_logs($table_name): void
    {
        Schema::connection('mysql2')->create($table_name, function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('order_id');
            $table->enum('status', ['wait_for_payment','in_progress','delivered','new','canceled','failed','reserved']);
            $table->string('creatorable_type');
            $table->unsignedBigInteger('creatorable_id');
            $table->string('updaterable_type');
            $table->unsignedBigInteger('updaterable_id');
            $table->timestamps();
        });
    }

    private function create_table_webhook_logs($table_name): void
    {
        Schema::connection('mysql2')->create($table_name, function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->enum('type', ['send','receive']);
            $table->longText('data');
            $table->string('ip');
            $table->string('message')->nullable();
            $table->text('extra')->nullable();
            $table->timestamps();
        });
    }









    private function query_creator($table, $all_rows): array /* this method unused */
    {
        $queries = [];
        // count rows and calculate that how many queries should create?
        // we seprate rows with max_rows_per_query. it means we have limited rows in one query. we seperated it to many queries to save server resources


        /*$all_rows->chunk($this->max_rows_per_query)->each(function ($rows) use($table, $queries)
        {
            // Process $chunckedRows (which contains max_rows_per_query elements)

            // for query at first we should fill "insert into" part and then we should fill values
            $query = "INSERT INTO " . $table . " (";
            foreach ($rows[0] as $attribute => $value) // get this tables keys. and fill it into query.
            {
                if ($attribute === key(array_slice($rows[0], -1, 1, true))) // if it was the latest field of foreach loop
                { $query .= $attribute . ") VALUES ( "; }
                else { $query .= $attribute . ", "; }
            }
            dd($query);

            $rows->each(function ($row) use($table, $query, $queries)
            {
                $here_query = $query;
                // fill values into the query
                foreach ($row as $attribute => $value)
                {
                    if ($attribute === key(array_slice($row, -1, 1, true))) // if it was the latest field of foreach loop
                    { $here_query .= $value . ")"; }
                    else { $here_query .= $value . ", "; }
                }
                $queries[] = $here_query;

            });
        });*/



        $chunckedRows = $all_rows->chunk($this->max_rows_per_query);
        // limit count of rows
        // here we create one query
        foreach ($chunckedRows as $here_rows)
        {
            // for query at first we should fill "insert into" part and then we should fill values
            $query = "INSERT INTO " . $table . " (";
            $is_it_first = true;
            foreach ($here_rows[0] as $attribute => $value) // get this tables keys. and fill it into query.
            {
                if ($is_it_first)
                {
                    $query .= "'" . $attribute . "'";
                    $is_it_first = false;
                }
                else
                {
                    $query .= ", '" . $attribute . "'";
                }
            }
            $query .= ") VALUES ( ";
            $is_first_row = true;

            foreach ($here_rows as $row)
            {
                if (!$is_first_row)
                {
                    $query .= "), (";
                }
                $is_first_row = false;
                // fill values into the query
                $is_it_first = true;
                foreach ($row as $attribute => $value)
                {
                    if ($is_it_first)
                    {
                        $query .= ("'" . $value . "'");
                        $is_it_first = false;
                    }
                    else
                    {
                        $query .= (", '" . $value . "'");
                    }
                }
            }
            $query .= ")";
            array_push($queries, $query);
            dump($queries);

//            $queries[] = $query;
        }
        dd($queries);

        return $queries;
    }
}
