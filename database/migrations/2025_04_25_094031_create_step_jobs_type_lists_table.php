<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStepJobsTypeListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('step_jobs_type_lists', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('job_id')->unsigned()->index();
            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');

            $table->integer('step_jobs_id')->unsigned()->index();
            $table->foreign('step_jobs_id')->references('id')->on('step_jobs')->onDelete('cascade');

            $table->integer('work_type_id')->unsigned()->index()->comment('ประเภทค่าใช้จ่าย');
            $table->foreign('work_type_id')->references('id')->on('work_types')->onDelete('cascade');

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
        Schema::dropIfExists('step_jobs_type_lists');
    }
}
