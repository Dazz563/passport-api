<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImages extends Model
{
    use HasFactory;
    protected $table = "product_images";

    public function getImageAttribute($value)
    {
        return '/storage/product_images/' . $value;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
