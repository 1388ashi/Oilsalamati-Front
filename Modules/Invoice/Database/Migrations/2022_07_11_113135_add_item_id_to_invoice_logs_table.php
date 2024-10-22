<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddItemIdToInvoiceLogsTable extends Migration
{

    public function up()
    {
        Schema::table('invoice_logs', function (Blueprint $table) {
            $table->foreignId('item_id')->nullable()->after('customer_id')->constrained('order_items')->cascadeOnDelete();
        });
    }


    public function down()
    {
        Schema::table('invoice_logs', function (Blueprint $table) {
            $table->dropColumn('item_id');
        });
    }
}
