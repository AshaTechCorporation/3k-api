<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DebtorAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'debtor_accounts';

    public function payments()
    {
        return $this->hasMany(DebtorPayment::class, 'debtor_account_id');
    }
}
