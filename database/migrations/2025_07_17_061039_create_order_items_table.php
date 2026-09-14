<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade'); // Mỗi item thuộc 1 order
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Liên kết sản phẩm
            $table->integer('quantity'); // Số lượng
            $table->decimal('price', 15, 2); // Giá tại thời điểm mua
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
