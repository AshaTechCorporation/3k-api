<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_trans', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 50)->charset('utf8')->nullable();

            $table->integer('product_id')->nullable();

            $table->integer('qty')->nullable();

            $table->enum('source', ['Factory', 'Purchase'])->charset('utf8')->default('Factory');

            $table->string('create_by', 100)->charset('utf8')->nullable();
            $table->string('update_by', 100)->charset('utf8')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_trans');
    }
}
