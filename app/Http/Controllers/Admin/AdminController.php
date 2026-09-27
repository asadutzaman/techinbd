<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductOptimized;
use App\Models\User;
use Illuminate\Support\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        $tz = config('shop.timezone');
        $monthStart = now($tz)->startOfMonth()->utc();
        // Revenue and order counts leave out cancelled orders
        $live = fn () => Order::where('status', '!=', 'cancelled');

        return view('admin.dashboard', [
            'stats' => [
                'revenue_month' => (float) $live()->where('created_at', '>=', $monthStart)->sum('total'),
                'orders_month' => $live()->where('created_at', '>=', $monthStart)->count(),
                'pending' => Order::where('status', 'pending')->count(),
                'to_ship' => Order::where('status', 'processing')->count(),
                'products' => ProductOptimized::active()->count(),
                'out_of_stock' => ProductOptimized::active()->where('stock_status', 'out_of_stock')->count(),
                'customers' => User::where('is_admin', false)->count(),
                'failed_emails' => Order::latestEmailFailed()->count(),
            ],
            'sales' => $this->dailySales(14),
            'recentOrders' => Order::latest()->orderByDesc('id')->take(8)->get(),
            'lowStock' => ProductOptimized::active()
                ->where('manage_stock', true)
                ->where('total_stock', '<=', 3)
                ->orderBy('total_stock')
                ->orderBy('name')
                ->take(6)
                ->get(['id', 'name', 'sku', 'total_stock', 'stock_status']),
        ]);
    }

    /**
     * Order totals per day for the last $days days, in shop time (oldest first).
     */
    private function dailySales(int $days): array
    {
        $tz = config('shop.timezone');
        $first = now($tz)->startOfDay()->subDays($days - 1);

        // Grouped in PHP so the day boundaries follow the shop's timezone on any database
        $byDay = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $first->copy()->utc())
            ->get(['total', 'created_at'])
            ->groupBy(fn (Order $order) => $order->created_at->timezone($tz)->toDateString());

        $sales = [];
        for ($day = $first->copy(); $day->lte(now($tz)); $day->addDay()) {
            $orders = $byDay->get($day->toDateString(), collect());
            $sales[] = [
                'date' => Carbon::parse($day->toDateString(), $tz),
                'total' => (float) $orders->sum('total'),
                'orders' => $orders->count(),
            ];
        }

        return $sales;
    }
}
