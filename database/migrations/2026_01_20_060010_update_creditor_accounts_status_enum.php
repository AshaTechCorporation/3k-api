<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateCreditorAccountsStatusEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE creditor_accounts MODIFY status ENUM('unpaid','partial','paid') DEFAULT 'unpaid'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE creditor_accounts MODIFY status ENUM('unpaid','paid') DEFAULT 'unpaid'");
    }
}
