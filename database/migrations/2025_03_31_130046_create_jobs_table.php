<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('product_id')->unsigned()->index();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            
            $table->integer('work_type_id')->unsigned()->index();
            $table->foreign('work_type_id')->references('id')->on('work_types')->onDelete('cascade');
            
            $table->decimal('estimated_cost', 10, 2)->default(0)->comment('ค่าใช้จ่ายประมาณการ');
            $table->text('remark')->nullable()->comment('หมายเหตุเพิ่มเติม');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');

            $table->date('completed_date')->nullable()->comment('วันที่ดำเนินการเสร็จ');

            $table->unsignedInteger('current_step_id')->nullable()->comment('ประเภทงานในขณะนั้น');

            $table->enum('master', ['Y','N'])->charset('utf8')->default('N');
            $table->text('master_name')->nullable()->comment('ชื่อ');

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
        Schema::dropIfExists('jobs');
    }
}
