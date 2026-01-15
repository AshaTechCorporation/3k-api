<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckListCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_list_categories', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('category_product_id')->unsigned()->index();
            $table->foreign('category_product_id')->references('id')->on('category_products')->onDelete('cascade');

            $table->integer('check_list_id')->unsigned()->index();
            $table->foreign('check_list_id')->references('id')->on('check_lists')->onDelete('cascade');

            $table->string('create_by', 100)->nullable();
            $table->string('update_by', 100)->nullable();

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
        Schema::dropIfExists('check_list_categories');
    }
}
