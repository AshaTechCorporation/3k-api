<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditorAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('creditor_accounts', function (Blueprint $table) {
            $table->increments('id');

            $table->string('vendor_name', 250)->charset('utf8');

            $table->decimal('credit_amount', 15, 2)->default(0)->comment('ยอดเครดิตตั้งต้น');
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');

            $table->date('credit_date')->comment('วันที่ตั้งเครดิต');
            $table->text('note')->nullable()->charset('utf8');

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
        Schema::dropIfExists('creditor_accounts');
    }
}
