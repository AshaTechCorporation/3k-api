<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RenameTransactionsCategoryId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropIndex(['category_id']);
        });

        DB::statement('ALTER TABLE transactions CHANGE category_id income_expenses_tracker_type_id INT UNSIGNED NULL');

        Schema::table('transactions', function (Blueprint $table) {
            $table->index('income_expenses_tracker_type_id');
            $table->foreign('income_expenses_tracker_type_id')
                ->references('id')
                ->on('income_expenses_tracker_types')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['income_expenses_tracker_type_id']);
            $table->dropIndex(['income_expenses_tracker_type_id']);
        });

        DB::statement('ALTER TABLE transactions CHANGE income_expenses_tracker_type_id category_id INT UNSIGNED NULL');

        Schema::table('transactions', function (Blueprint $table) {
            $table->index('category_id');
            $table->foreign('category_id')
                ->references('id')
                ->on('income_expenses_tracker_types')
                ->onDelete('set null');
        });
    }
}
