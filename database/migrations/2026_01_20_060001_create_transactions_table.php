<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');

            $table->date('tx_date')->comment('วันที่ทำรายการ');
            $table->enum('tx_type', ['income', 'expense']);
            $table->enum('payment_method', ['cash', 'transfer', 'credit']);
            $table->decimal('amount', 15, 2)->default(0)->comment('ยอดรวมที่เข้าหรือออกจริง');

            $table->integer('category_id')->unsigned()->nullable()->index();

            $table->text('description')->nullable()->charset('utf8');
            $table->enum('related_type', ['debtor', 'creditor'])->nullable();
            $table->integer('related_id')->unsigned()->nullable()->index();

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
        Schema::dropIfExists('transactions');
    }
}
