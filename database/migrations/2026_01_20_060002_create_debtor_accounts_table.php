<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDebtorAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('debtor_accounts', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('debtor_user_id')->unsigned()->nullable()->index();
            $table->string('debtor_name', 250)->charset('utf8')->nullable();

            $table->decimal('principal_amount', 15, 2)->default(0)->comment('ยอดตั้งต้น');
            $table->decimal('principal_paid', 15, 2)->default(0);
            $table->decimal('interest_paid', 15, 2)->default(0);
            $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid');

            $table->date('start_date')->comment('วันที่เริ่มต้น');
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
        Schema::dropIfExists('debtor_accounts');
    }
}
