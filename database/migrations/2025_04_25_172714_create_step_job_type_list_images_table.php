<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStepJobTypeListImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('step_job_type_list_images', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('step_jobs_type_list_id')->unsigned()->index();
            $table->foreign('step_jobs_type_list_id')->references('id')->on('step_jobs_type_lists')->onDelete('cascade');

            $table->text('image')->nullable()->charset('utf8');
       
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
        Schema::dropIfExists('step_job_type_list_images');
    }
}
