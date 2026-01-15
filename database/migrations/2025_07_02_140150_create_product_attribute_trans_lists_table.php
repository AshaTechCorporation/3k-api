<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductAttributeTransListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_attribute_trans_lists', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('product_attribute_tran_id')->unsigned()->index();
            $table->foreign('product_attribute_tran_id')->references('id')->on('product_attribute_trans')->onDelete('cascade');

            $table->integer('step_jobs_type_list_id')->nullable();
            $table->integer('work_type_id')->nullable();

            $table->integer('product_attribute_id')->unsigned()->index();
            $table->foreign('product_attribute_id')->references('id')->on('product_attributes')->onDelete('cascade');

            $table->integer('qty');

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
        Schema::dropIfExists('product_attribute_trans_lists');
    }
}
