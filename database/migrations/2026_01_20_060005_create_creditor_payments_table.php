<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditorPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('creditor_payments', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('creditor_account_id')->unsigned()->index();
            $table->foreign('creditor_account_id')->references('id')->on('creditor_accounts')->onDelete('cascade');

            $table->integer('transaction_id')->unsigned()->index();
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');

            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->date('paid_date')->comment('วันที่จ่าย');
            $table->enum('payment_method', ['cash', 'transfer']);

            $table->string('create_by', 100)->charset('utf8')->nullable();
            $table->string('update_by', 100)->charset('utf8')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('creditor_payments');
    }
}
