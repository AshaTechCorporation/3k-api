<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttributeTrans extends Model
{
    use HasFactory;

    public function product_attribute_trans_lists()
    {
        return $this->hasMany(ProductAttributeTransList::class, 'product_attribute_tran_id');
    }

    public function job()
    {
        return $this->belongsTo(Jobs::class, 'job_id');
    }
}
