<tbody>
@forelse($products as $product)
    <tr>
        <td>{{ $product->id }}</td>
        <td>{{ $product->name }}</td>
        <td>{{ $product->category->name ?? 'Không có' }}</td>
        <td>{{ number_format($product->price, 0, ',', '.') }} VNĐ</td> <!-- ✅ giá -->
        <td>{{ $product->quantity }}</td> <!-- ✅ số lượng -->
        <td>
            <a href="{{ route('user.products.show', $product->id) }}" class="btn btn-primary btn-sm>">Xem</a>
            <a href="{{ route('user.products.edit', $product->id) }}" class="btn btn-primary btn-sm">Sửa</a>
            <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-sm" onclick="return confirm('Xóa sản phẩm này?')">Xóa</button>
            </form>
        </td>
    </tr>
@empty
    <tr><td colspan="6" class="text-center text-muted">Chưa có sản phẩm nào.</td></tr> <!-- ✅ chỉnh colspan -->
@endforelse
</tbody>
