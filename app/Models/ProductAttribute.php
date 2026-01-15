<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    use HasFactory;

    public function company() { return $this->belongsTo(Company::class, 'companie_id'); }
    public function area() { return $this->belongsTo(Area::class); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function categoryProduct() { return $this->belongsTo(CategoryProduct::class, 'category_product_id'); }
    public function images() { return $this->hasMany(ProductImages::class, 'product_id'); }
    public function jobs() { return $this->hasMany(Jobs::class, 'product_id'); }
    public function brand() { return $this->belongsTo(Brand::class); }
    public function brandModel() { return $this->belongsTo(BrandModel::class); }
    public function cc() { return $this->belongsTo(CC::class); }
    public function color() { return $this->belongsTo(Color::class); }
}
