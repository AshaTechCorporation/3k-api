<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebtorPayment extends Model
{
    use HasFactory;

    protected $table = 'debtor_payments';

    public function account()
    {
        return $this->belongsTo(DebtorAccount::class, 'debtor_account_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}
