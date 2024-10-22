<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBeforeAfterImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('before_after_images', function (Blueprint $table) {
            $table->id();
            $table->string('title',100)->nullable();
            $table->string('short_description',255)->nullable();
            $table->text('full_description')->nullable();
            $table->string('customer_name',50)->nullable();
            $table->string('uuid',36);
            $table->unsignedBigInteger('product_id');
            $table->enum('type',['before','after']);
            $table->boolean('enabled')->default(1);
            $table->timestamps();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('before_after_images');
    }
}
