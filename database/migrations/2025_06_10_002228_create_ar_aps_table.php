<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArApsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ar_aps', function (Blueprint $table) {
            $table->increments('id');

            $table->string('code')->unique(); // รหัสเอกสาร เช่น TR-0001
            $table->enum('partner_type', ['debtor', 'creditor']); // ระบุประเภทคู่ค้า
            $table->string('partner_name'); // ชื่อลูกหนี้หรือเจ้าหนี้

            $table->date('transaction_date');
            $table->enum('direction', ['in', 'out']); // 'in' = รับเงิน, 'out' = จ่ายเงิน
            $table->decimal('amount', 15, 2); // จำนวนเงิน

            $table->enum('status', ['pending', 'paid'])->default('pending');

            $table->text('description')->nullable(); // หมายเหตุ

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
        Schema::dropIfExists('ar_aps');
    }
}
