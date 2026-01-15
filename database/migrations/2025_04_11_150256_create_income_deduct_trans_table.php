<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomeDeductTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('income_deduct_trans', function (Blueprint $table) {
            $table->increments('id');

            $table->date('transaction_date')->comment('วันที่ทำรายการ');

            $table->enum('type', ['income', 'expense'])->comment('ประเภทรายการ (รายรับ, รายจ่าย)');

            $table->string('category')->comment('หมวดหมู่ เช่น ค่าจ้าง, ค่าน้ำมัน');

            $table->text('description')->nullable()->comment('รายละเอียด');

            $table->decimal('amount', 15, 2)->comment('จำนวนเงิน');

            $table->enum('payment_method', ['cash', 'transfer'])->comment('ประเภทเงิน เช่น เงินสด, เงินโอน');

            $table->string('attachment')->nullable()->comment('แนบไฟล์หลักฐาน (ถ้ามี)');

            $table->integer('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

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
        Schema::dropIfExists('income_deduct_trans');
    }
}
