<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 50)->charset('utf8')->nullable();

            $table->integer('category_product_id')->unsigned()->index();
            $table->foreign('category_product_id')->references('id')->on('category_products')->onDelete('cascade');

            $table->integer('area_id')->nullable();
            $table->integer('brand_id')->nullable();
            $table->integer('brand_model_id')->nullable();

            $table->string('serial_no', 50)->charset('utf8')->nullable();

            $table->text('name')->charset('utf8');
            $table->text('detail')->nullable()->charset('utf8');
            $table->integer('qty')->nullable();

            $table->enum('book', ['Y','N'])->charset('utf8')->default('N');

            $table->enum('status', ['active', 'unactive'])->charset('utf8')->default('active');

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
        Schema::dropIfExists('products');
    }
}
