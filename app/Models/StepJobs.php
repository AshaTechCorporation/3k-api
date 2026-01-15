<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StepJobs extends Model
{
    use HasFactory;

    public function stepJobTypeLists()
    {
        return $this->hasMany(StepJobsTypeLists::class, 'step_jobs_id');
    }
}
