<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderChangeStatusJobManagersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_change_status_job_managers', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('cron_job_uuid');
            $table->timestamp('run_time');
            $table->enum('status', \Modules\Order\Entities\OrderChangeStatusJobManager::getAllStatuses());
            $table->boolean('is_master');
            $table->json('all_order_ids')->nullable();
            $table->json('order_ids_done')->nullable();




            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_change_status_job_managers');
    }
}
