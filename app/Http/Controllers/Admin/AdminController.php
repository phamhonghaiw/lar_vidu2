<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Thống kê tổng số tài khoản có role là 'user'
        $totalUsers = User::where('role', 'user')->count();

        // Thống kê tổng số đơn hàng
        $totalOrders = Order::count();

        // Thống kê tổng doanh thu
        $totalRevenue = Order::sum('total_price');

        // Báo cáo 7 ngày gần nhất (từ 6 ngày trước đến hôm nay)
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $last7Days->put($date, [
                'date_label' => Carbon::parse($date)->format('d/m'),
                'full_date'  => $date,
                'new_users'  => 0,
                'new_orders' => 0,
                'revenue'    => 0,
            ]);
        }

        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate   = Carbon::now()->endOfDay();

        // Lấy số người đăng ký mới theo từng ngày
        $newUsersByDay = User::where('role', 'user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
            ->groupBy('date')
            ->pluck('total', 'date');

        // Lấy số đơn hàng mới và doanh thu theo từng ngày
        $ordersByDay = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_price) as total_revenue')
            )
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Tổng hợp dữ liệu vào danh sách 7 ngày
        $report7Days = $last7Days->map(function ($item, $date) use ($newUsersByDay, $ordersByDay) {
            $item['new_users']  = $newUsersByDay->get($date, 0);
            $item['new_orders'] = $ordersByDay->has($date) ? $ordersByDay->get($date)->total_orders : 0;
            $item['revenue']    = $ordersByDay->has($date) ? (float)$ordersByDay->get($date)->total_revenue : 0;
            return $item;
        });

        $totalNewUsers7Days  = $report7Days->sum('new_users');
        $totalNewOrders7Days = $report7Days->sum('new_orders');
        $totalRevenue7Days   = $report7Days->sum('revenue');

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalOrders',
            'totalRevenue',
            'report7Days',
            'totalNewUsers7Days',
            'totalNewOrders7Days',
            'totalRevenue7Days'
        ));
    }
}
