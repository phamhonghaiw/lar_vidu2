<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        /**
         * Nếu muốn chỉ tính doanh thu cho đơn đã giao xong,
         * thay whereIn('orders.status', [...]) bằng:
         * ->where('orders.shipping_status', 'completed')
         */
        $paidStatuses = ['cod_ordered', 'paid_momo'];

        // 1) Doanh thu theo danh mục (category_id) từ order_items
        $categoryRevenue = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', $paidStatuses) // hoặc ->where('orders.shipping_status', 'completed')
            ->select(
                'products.category_id',
                DB::raw('SUM(order_items.price * order_items.quantity) AS total_revenue'),
                DB::raw('SUM(order_items.quantity) AS total_qty')
            )
            ->groupBy('products.category_id')
            ->orderByDesc('total_revenue')
            ->get();

        // 2) Tổng số đơn hàng (tất cả trạng thái)
        $totalOrders = Order::count();

        // 3) Tổng số khách hàng (giả định cột role='customer')
        $totalCustomers = DB::table('users')->where('role', 'customer')->count();

        // 4) Doanh thu theo ngày: SUM(orders.total_price)
        $revenueByDate = Order::query()
            ->whereIn('status', $paidStatuses) // hoặc ->where('shipping_status', 'completed')
            ->selectRaw('DATE(created_at) AS date, SUM(total_price) AS total_revenue, COUNT(*) AS order_count')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // 5) Doanh thu theo tháng (YYYY-MM)
        $revenueByMonth = Order::query()
            ->whereIn('status', $paidStatuses)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") AS month, SUM(total_price) AS total_revenue, COUNT(*) AS order_count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // 6) Doanh thu theo năm
        $revenueByYear = Order::query()
            ->whereIn('status', $paidStatuses)
            ->selectRaw('YEAR(created_at) AS year, SUM(total_price) AS total_revenue, COUNT(*) AS order_count')
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        return view('admin.reports.index', compact(
            'categoryRevenue',
            'totalOrders',
            'totalCustomers',
            'revenueByDate',
            'revenueByMonth',
            'revenueByYear'
        ));
    }

    public function charts()
        {
            $paid = ['paid_momo', 'cod_ordered'];

            /* 1) Doanh thu theo danh mục */
            $byCat = DB::table('order_items')
                ->join('orders','order_items.order_id','=','orders.id')
                ->join('products','order_items.product_id','=','products.id')
                ->leftJoin('categories','products.category_id','=','categories.id')
                ->whereIn('orders.status',$paid)
                ->selectRaw('COALESCE(categories.name, CONCAT("Category #", products.category_id)) AS name')
                ->selectRaw('SUM(order_items.price * order_items.quantity) AS revenue')
                ->groupBy('name')
                ->orderByDesc('revenue')
                ->get();

            $catLabels  = $byCat->pluck('name')->toArray();
            $catRevenue = $byCat->pluck('revenue')->map(fn($v)=>(float)$v)->toArray();

            /* 2) Doanh thu theo ngày (30 ngày) */
            $startDay = Carbon::now()->subDays(29)->startOfDay();
            $endDay   = Carbon::now()->endOfDay();
            $byDate = Order::whereIn('status',$paid)
                ->whereBetween('created_at',[$startDay,$endDay])
                ->selectRaw('DATE(created_at) d, SUM(total_price) revenue')
                ->groupBy('d')->orderBy('d')->get()->keyBy('d');

            $revDateLabels = [];
            $revDateData   = [];
            for ($i=0; $i<30; $i++) {
                $d = $startDay->copy()->addDays($i)->toDateString();
                $revDateLabels[] = $d;
                $revDateData[]   = (float) ($byDate[$d]->revenue ?? 0);
            }

            /* 3) Doanh thu theo tháng (12 tháng) */
            $startMonth = Carbon::now()->subMonths(11)->startOfMonth();
            $byMonth = Order::whereIn('status',$paid)
                ->where('created_at','>=',$startMonth)
                ->selectRaw("DATE_FORMAT(created_at,'%Y-%m') ym, SUM(total_price) revenue")
                ->groupBy('ym')->orderBy('ym')->get()->keyBy('ym');

            $revMonthLabels = [];
            $revMonthData   = [];
            for ($i=0; $i<12; $i++) {
                $m   = $startMonth->copy()->addMonths($i);
                $key = $m->format('Y-m');
                $revMonthLabels[] = $m->format('m/Y');
                $revMonthData[]   = (float) ($byMonth[$key]->revenue ?? 0);
            }

            /* 4) Doanh thu theo năm */
            $byYear = Order::whereIn('status',$paid)
                ->selectRaw('YEAR(created_at) y, SUM(total_price) revenue')
                ->groupBy('y')->orderBy('y')->get();

            $revYearLabels = $byYear->pluck('y')->toArray();
            $revYearData   = $byYear->pluck('revenue')->map(fn($v)=>(float)$v)->toArray();

            /* 5) Doanh thu theo phương thức thanh toán */
            $paymentMethodLabels  = ['MoMo','COD'];
            $paymentMethodRevenue = [
                (float) Order::where('status','paid_momo')->sum('total_price'),
                (float) Order::where('status','cod_ordered')->sum('total_price'),
            ];

            return view('admin.reports.charts', compact(
                'catLabels','catRevenue',
                'revDateLabels','revDateData',
                'revMonthLabels','revMonthData',
                'revYearLabels','revYearData',
                'paymentMethodLabels','paymentMethodRevenue'
            ));
        }
}
