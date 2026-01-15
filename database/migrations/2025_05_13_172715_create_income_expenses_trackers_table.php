<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomeExpensesTrackersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('income_expenses_trackers', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('income_expenses_tracker_type_id')->unsigned()->index();
            $table->foreign('income_expenses_tracker_type_id')->references('id')->on('income_expenses_tracker_types')->onDelete('cascade');

            $table->string('name', 250)->charset('utf8')->nullable();
            $table->text('detail')->charset('utf8')->nullable();
            $table->string('image', 250)->charset('utf8')->nullable();
            
            $table->date('date')->comment('วันที่ทำรายการ');

            $table->decimal('amount', 10, 2)->default(0)->comment('ราคา');
            $table->enum('type', ['income', 'expense']);

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
        Schema::dropIfExists('income_expenses_trackers');
    }
}
