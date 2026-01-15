<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    public function orderList()
    {
        return $this->hasMany(OrderList::class, 'order_id');
    }

    public function checkLists()
    {
        return $this->hasMany(OrderCheck::class, 'order_id');
    }

    public function incomeExpenses()
    {
        return $this->hasMany(IncomeDeductTrans::class, 'order_id');
    }
}
