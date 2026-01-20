<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditorPayment extends Model
{
    use HasFactory;

    protected $table = 'creditor_payments';

    public function account()
    {
        return $this->belongsTo(CreditorAccount::class, 'creditor_account_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}
