<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StepJobsTypeLists extends Model
{
    use HasFactory;

    public function productAttributes()
    {
        return $this->hasMany(StepJobsTypeProductAttribute::class, 'step_jobs_type_list_id');
    }

    public function productAttributeOthers()
    {
        return $this->hasMany(StepJobsTypeProAttrOther::class, 'step_jobs_type_list_id');
    }

    public function expenses()
    {
        return $this->hasMany(StepJobsTypeExpense::class, 'step_jobs_type_list_id');
    }

    public function workType()
    {
        return $this->belongsTo(WorkType::class, 'work_type_id');
    }

    public function images()
    {
        return $this->hasMany(StepJobTypeListImages::class, 'step_jobs_type_list_id');
    }
}
