<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('companie_id')->unsigned()->index();
            $table->foreign('companie_id')->references('id')->on('companies')->onDelete('cascade');
            
            $table->string('name', 250)->charset('utf8')->nullable();
            $table->text('detail')->charset('utf8')->nullable();
            $table->text('address')->charset('utf8')->nullable();
            $table->string('image', 250)->charset('utf8')->nullable();

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
        Schema::dropIfExists('areas');
    }
}
