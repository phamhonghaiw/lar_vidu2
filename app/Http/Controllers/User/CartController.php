<?php

namespace App\Http\Controllers\User;



use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class CartController extends Controller
{
    // Thêm sản phẩm vào giỏ
    public function add(Product $product)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity']++;
        } else {
            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price ?? 0,
                'quantity' => 1,
            ];
        }

        session()->put('cart', $cart);

        return redirect()->back()->with('success', 'Đã thêm vào giỏ hàng!');
    }

    // Xem giỏ hàng
    public function index()
    {
        $cart = session()->get('cart', []);
        return view('user.cart.index', compact('cart'));
    }

    // Xóa 1 sản phẩm khỏi giỏ
    public function remove($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        return redirect()->back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    // Xóa toàn bộ giỏ
    public function clear()
    {
        session()->forget('cart');
        return redirect()->back()->with('success', 'Đã xóa toàn bộ giỏ hàng.');
    }

    public function update(Request $request, $id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] = $request->quantity;
            session()->put('cart', $cart);
            return redirect()->back()->with('success', 'Cập nhật số lượng thành công.');
        }

        return redirect()->back()->with('success', 'Sản phẩm không tồn tại.');
    }
   
   
   
    // public function checkout()
    // {
    //     $cart = session()->get('cart', []);

    //     if (empty($cart)) {
    //         return redirect()->back()->with('error', 'Giỏ hàng trống. Không thể thanh toán.');
    //     }

    //     // Ở đây bạn có thể xử lý lưu đơn hàng vào database nếu cần.

    //     // Xóa giỏ hàng sau khi thanh toán
    //     session()->forget('cart');

    //     return redirect()->route('user.cart.index')->with('success', 'Thanh toán thành công!');
    // }


}
