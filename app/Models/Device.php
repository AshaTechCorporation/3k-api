<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'device';
    protected $softDelete = true;

    protected $hidden = ['deleted_at'];

    //////////////////////////////////////// format //////////////////////////////////////

    // protected function serializeDate(DateTimeInterface $date)
    // {
    //     return $date->format('Y-m-d H:i:s');
    // }

    //////////////////////////////////////// relation //////////////////////////////////////

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function frammer()
    {
        return $this->belongsTo(Frammers::class, 'qouta_id', 'qouta_id');
    }
}
