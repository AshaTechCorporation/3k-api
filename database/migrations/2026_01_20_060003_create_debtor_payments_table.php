<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDebtorPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('debtor_payments', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('debtor_account_id')->unsigned()->index();
            $table->foreign('debtor_account_id')->references('id')->on('debtor_accounts')->onDelete('cascade');

            $table->integer('transaction_id')->unsigned()->index();
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');

            $table->integer('installment_no')->unsigned()->default(1);
            $table->decimal('principal_paid', 15, 2)->default(0);
            $table->decimal('interest_paid', 15, 2)->default(0);
            $table->date('payment_date')->comment('วันที่ชำระ');

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
        Schema::dropIfExists('debtor_payments');
    }
}
