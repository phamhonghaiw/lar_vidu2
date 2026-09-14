<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    protected $table = 'products'; // tên bảng

    protected $fillable = [
        'name', 'category_id', 'price', 'quantity'
    ];

    // Mối quan hệ: mỗi sản phẩm thuộc 1 danh mục
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}