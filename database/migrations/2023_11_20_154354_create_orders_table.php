<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');

            $table->string('code', 255)->charset('utf8');

            // ข้อมูลรถ
            $table->string('brand')->nullable()->charset('utf8');
            $table->string('model')->nullable()->charset('utf8');
            $table->string('color')->nullable()->charset('utf8');
            $table->string('license')->nullable()->charset('utf8');
            $table->string('province')->nullable()->charset('utf8');
            $table->integer('year')->nullable();

            // ข้อมูลลูกค้า
            $table->string('client_name')->nullable()->charset('utf8');
            $table->string('client_phone')->nullable()->charset('utf8');
            $table->string('client_id_card')->nullable()->charset('utf8');
            $table->text('client_address')->nullable()->charset('utf8');

            // วันจองและนัดรับ
            $table->date('booking_date')->nullable();
            $table->date('pickup_date')->nullable();

            // ช่องทางการขาย
            $table->string('sale_channel')->nullable()->charset('utf8');

            // พนักงานขาย
            $table->integer('sale_id')->unsigned()->index();
            $table->foreign('sale_id')->references('id')->on('users')->onDelete('cascade');

            // การเงิน
            $table->decimal('down_payment', 10, 2)->nullable();
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->nullable();

            // หมายเหตุอื่น ๆ
            $table->text('sale_remark')->nullable()->charset('utf8');

            // สถานะคำสั่งซื้อ
            $table->enum('status', ['pending', 'deposit_received', 'waiting_for_finance', 'finance_approved', 'waiting_for_delivery', 'cancelled'])->default('pending')->charset('utf8');

            // ข้อมูลระบบ
            $table->string('create_by', 100)->nullable()->charset('utf8');
            $table->string('update_by', 100)->nullable()->charset('utf8');

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
        Schema::dropIfExists('orders');
    }
}
