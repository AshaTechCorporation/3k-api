<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeExpensesTracker extends Model
{
    use HasFactory;

    public function type()
    {
        return $this->belongsTo(IncomeExpensesTrackerType::class, 'income_expenses_tracker_type_id');
    }
}
