<?php

namespace App\Http\Controllers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransferToExtraController extends Controller
{
    public function transfer($tableName)
    {
        if (!Schema::connection('mysql')->hasTable($tableName))
            dd('this table not exists in main DB');

        $this->tableCreator($tableName);
        // check transferd exists in main table
        if (!Schema::connection('mysql')->hasColumn($tableName, 'transferd')) {
            Schema::connection('mysql')->table($tableName, function (Blueprint $table) {
                $table->boolean('transferd')->default(false);
            });
        }

        // get all data
        $allData = DB::connection('mysql')
            ->table($tableName)
            ->where('transferd', '=', false)
            ->orderBy('id', 'asc')
            ->limit(5000)
            ->get();

        $minId = $allData->min('id');
        $maxId = $allData->max('id');
        $allIdsPluck = $allData->pluck('id')->toArray();

        $dataTransferredCount = $allData->count();

        // create array for transfer
        $transferData = [];
        foreach ($allData as $data) {
            $oneItem = [];
            foreach ($data as $key => $value) {
                if ($key == 'id' || $key == "transferd") continue;
                $oneItem[$key] = $value;
            }
            $transferData[] = $oneItem;
        }
        // send to extra
        DB::connection('extra')->table($tableName)->insert($transferData);
        // update transferd field in main DB
        DB::connection('mysql')->table($tableName)->whereIn('id',$allIdsPluck)->update(['transferd'=>true]);
        $message = "NOT FINISHED. CALL AGAIN";
        if ($dataTransferredCount == 0) {
            Schema::connection('mysql')->dropIfExists($tableName);
            $message = "FINISHED and table dropped from main DB";
        }


        // show the result
        dd(compact('dataTransferredCount','allIdsPluck','minId','maxId'), $message);
    }

    private function tableCreator($tableName)
    {
        if (!in_array($tableName, ['jobs','failed_jobs','failed_jobs2','failed_jobs3','activity_log','shipping_excels','site_views','views']))
            dd('table name is wrong');

        if (Schema::connection('extra')->hasTable($tableName))
            return;

        switch ($tableName)
        {
            case "jobs":
                Schema::connection('extra')->create('jobs', function (Blueprint $table) {
                    $table->bigIncrements('id');
                    $table->string('queue')->index();
                    $table->longText('payload');
                    $table->unsignedTinyInteger('attempts');
                    $table->unsignedInteger('reserved_at')->nullable();
                    $table->unsignedInteger('available_at');
                    $table->unsignedInteger('created_at');
                });
                break;
            case "failed_jobs":
                Schema::connection('extra')->create('failed_jobs', function (Blueprint $table) {
                    $table->id();
                    $table->string('uuid');
                    $table->text('connection');
                    $table->string('queue');
                    $table->string('payload');
                    $table->bigInteger('exception');
                    $table->timestamp('failed_at');
                });
                break;
            case "failed_jobs2":
                Schema::connection('extra')->create('failed_jobs2', function (Blueprint $table) {
                    $table->id();
                    $table->string('uuid');
                    $table->text('connection');
                    $table->string('queue');
                    $table->string('payload');
                    $table->bigInteger('exception');
                    $table->timestamp('failed_at');
                });
                break;
            case "failed_jobs3":
                Schema::connection('extra')->create('failed_jobs3', function (Blueprint $table) {
                    $table->id();
                    $table->string('uuid');
                    $table->text('connection');
                    $table->string('queue');
                    $table->string('payload');
                    $table->bigInteger('exception');
                    $table->timestamp('failed_at');
                });
                break;
            case "activity_log":
                Schema::connection('extra')->create('activity_log', function (Blueprint $table) {
                    $table->id();
                    $table->string('log_name');
                    $table->text('description')->nullable();
                    $table->string('subject_type');
                    $table->string('event');
                    $table->bigInteger('subject_id');
                    $table->string('causer_type')->nullable();
                    $table->bigInteger('causer_id')->nullable();
                    $table->longText('properties');
                    $table->char('batch_uuid')->nullable();
                    $table->timestamps();
                });
                break;
            case "shipping_excels":
                Schema::connection('extra')->create('shipping_excels', function (Blueprint $table) {
                    $table->id();
                    $table->string('title')->nullable();
                    $table->string('barcode')->nullable();
                    $table->string('repository')->nullable();
                    $table->string('register_date')->nullable();
                    $table->string('special_services')->nullable();
                    $table->string('destination')->nullable();
                    $table->integer('reference_number');
                    $table->string('receiver_name')->nullable();
                    $table->string('sender_name')->nullable();
                    $table->integer('price');
                    $table->unsignedBigInteger('creator_id')->nullable();
                    $table->unsignedBigInteger('updater_id')->nullable();
                    $table->timestamps();
                });
                break;
            case "site_views":
                Schema::connection('extra')->create('site_views', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('count');
                    $table->integer('hour');
                    $table->date('date');
                });
                break;
            case "views":
                Schema::connection('extra')->create('views', function (Blueprint $table) {
                    $table->id();
                    $table->string('viewable_type');
                    $table->unsignedBigInteger('viewable_id');
                    $table->text('visitor')->nullable();
                    $table->string('ip')->nullable();
                    $table->string('collection')->nullable();
                    $table->timestamp('viewed_at');
                });
                break;
            default:
                dd('table name is wrong');
                break;
        }

    }
}
