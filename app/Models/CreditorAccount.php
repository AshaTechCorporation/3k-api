<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditorAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'creditor_accounts';

    public function payments()
    {
        return $this->hasMany(CreditorPayment::class, 'creditor_account_id');
    }
}
