<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsExpensesListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs_expenses_lists', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('job_id')->unsigned()->index();
            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');

            $table->integer('expense_type_id')->unsigned()->index()->comment('ประเภทค่าใช้จ่าย');
            $table->foreign('expense_type_id')->references('id')->on('expense_types')->onDelete('cascade');

            $table->text('description')->nullable()->comment('รายละเอียดค่าใช้จ่าย');
            $table->decimal('amount', 10, 2)->default(0)->comment('ราคา');

            $table->integer('product_attribute_id')->nullable();
            $table->integer('product_attribute_qty')->nullable();

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
        Schema::dropIfExists('jobs_expenses_lists');
    }
}
