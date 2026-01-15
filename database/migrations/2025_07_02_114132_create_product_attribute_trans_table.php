<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductAttributeTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_attribute_trans', function (Blueprint $table) {
            $table->increments('id');

            $table->string('code', 255)->charset('utf8');
            $table->integer('job_id')->nullable();
           
            $table->text('remark')->nullable();

            // เพิ่มฟิลด์สถานะ
            $table->enum('status', ['draft', 'approved', 'rejected', 'completed', 'cancelled'])->default('draft');
            
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
        Schema::dropIfExists('product_attribute_trans');
    }
}
