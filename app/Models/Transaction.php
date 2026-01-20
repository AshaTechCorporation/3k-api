<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'transactions';

    public function debtorPayment()
    {
        return $this->hasOne(DebtorPayment::class, 'transaction_id');
    }

    public function debtorAccount()
    {
        return $this->belongsTo(DebtorAccount::class, 'related_id');
    }

    public function category()
    {
        return $this->belongsTo(IncomeExpensesTrackerType::class, 'income_expenses_tracker_type_id');
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class, 'transaction_id');
    }

    public function creditorPayment()
    {
        return $this->hasOne(CreditorPayment::class, 'transaction_id');
    }

    public function creditorAccount()
    {
        return $this->belongsTo(CreditorAccount::class, 'related_id');
    }
}
