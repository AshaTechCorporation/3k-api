<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jobs extends Model
{
    use HasFactory;

    public function images()
    {
        return $this->hasMany(JobsImages::class, 'job_id');
    }

    public function otherExpenses()
    {
        return $this->hasMany(JobsExpensesList::class, 'job_id');
    }

    public function steps()
    {
        return $this->hasMany(StepJobs::class, 'job_id');
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }
}
