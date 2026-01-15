<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStepJobsTypeExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('step_jobs_type_expenses', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('job_id')->unsigned()->index();
            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');

            $table->integer('step_jobs_type_list_id')->unsigned()->index();
            $table->foreign('step_jobs_type_list_id')->references('id')->on('step_jobs_type_lists')->onDelete('cascade');

            $table->integer('work_type_id')->unsigned()->index()->comment('ประเภทค่าใช้จ่าย');
            $table->foreign('work_type_id')->references('id')->on('work_types')->onDelete('cascade');

            $table->decimal('amount', 10, 2)->default(0)->comment('ราคา');
            
            $table->text('detail')->charset('utf8')->nullable();

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
        Schema::dropIfExists('step_jobs_type_expenses');
    }
}
