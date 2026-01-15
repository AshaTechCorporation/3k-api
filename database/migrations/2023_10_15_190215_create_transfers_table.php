<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 50)->charset('utf8')->nullable();

            $table->integer('companie_id')->unsigned()->index();
            $table->foreign('companie_id')->references('id')->on('companies')->onDelete('cascade');

            $table->integer('area_id')->unsigned()->index();
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('cascade');

            $table->text('remark')->nullable()->charset('utf8');
            $table->enum('type', ['BuyIn', 'Transfer'])->charset('utf8')->default('Transfer');
            $table->enum('status', ['W', 'N', 'A'])->charset('utf8')->default('W');

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
        Schema::dropIfExists('transfers');
    }
}
