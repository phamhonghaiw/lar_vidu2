<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;

class WelcomeController extends Controller
{
    // Trang chủ / danh sách sản phẩm
    public function index()
    {
        $products = Product::with('category')->latest()->paginate(6);
        return view('welcome', compact('products'));
    }

    // Trang chi tiết sản phẩm
    public function detail(Product $product)
    {
        return view('product_detail', compact('product'));
    }
    
    public function categories()
    {
        $categories = Category::orderBy('name')->get();
        return view('user.categories.index', compact('categories'));
    }
}
