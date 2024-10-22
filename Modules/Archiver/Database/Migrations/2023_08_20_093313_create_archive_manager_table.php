<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArchiveManagerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('archive_manager', function (Blueprint $table) {
            $table->id();
            $table->string('tablename_source');
            $table->string('tablename_archive');
            $table->unsignedInteger('row_count');
            $table->unsignedBigInteger('id_start');
            $table->unsignedBigInteger('id_end');
//            $table->timestamp('created_at_start');
//            $table->timestamp('created_at_end');

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
        Schema::connection('mysql2')->dropIfExists('archive_manager');
    }
}
