<?php

namespace App\Http\Controllers\Cashier;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Order;
use App\Models\AddOns;
use App\Models\Payment;
use App\Models\OrderItem;
use App\Models\CustomerOrder;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\DailySalesReport;
use Illuminate\Pagination\LengthAwarePaginator;


class CashierController extends Controller
{
    public function cashierDashboard(Request $request)
    {
        $perPage = 50; // You can adjust the number per page as needed
    
        // Fetch unpaid orders with order items
        $customerOrders = Order::with('orderItems')
            ->where('status', 'Unpaid')
            ->latest()
            ->paginate($perPage);
    
        $addons = [];
    
        foreach ($customerOrders as $order) {
            // Fetch addons for each order
            $addons[$order->id] = AddOns::with('customizedProduct')
                ->where('order_id', $order->id)
                ->get();
        }
    
        return view('cashier.cashierDashboard', compact('customerOrders', 'addons'))
            ->with('i', ($request->input('page', 1) - 1) * $perPage);
    }

    public function order_payment(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->status = 'Paid';
        $order->updated_at = now();
        $order->save();

        $payments = new Payment();
        $payments->order_id	= $order->id;
        $payments->cashier_id = Auth::user()->id;
        // GCash payments have no "amount tendered" field, so fall back to the order total
        $payments->amount = $request->filled('amount') ? $request->amount : $order->total_amount;
        $payments->change = $request->filled('change') ? $request->change : 0;
        $payments->ref_num = $request->ref_num;
        $payments->save();

        Alert::success('Payment success');
        return redirect()->back();
    }

    protected function processPaidOrders()
{
    // Get paid orders that haven't been processed for the kitchen
    $paidOrders = Order::where('status', 'Paid')
        ->whereNull('updated_at') // Assuming 'processed_at' is a timestamp field
        ->orderBy('updated_at', 'asc') // Process in FIFO order based on payment time
        ->get();

    foreach ($paidOrders as $order) {
        // Logic to add order to the kitchen view (e.g., dispatching a job or updating a table)
        dispatch(new SendOrderToKitchen($order));
    
        // Mark the order as processed for the kitchen
        $order->processed_at = now();
        $order->save();
    }
}

    public function show($orderId)
{
    // Fetch the order details based on $orderId
    $order = Order::where('order_number', $orderId)->first();

    // Check if the order exists
    if (!$order) {
        abort(404); // Or handle the case where the order is not found
    }

    return view('order', compact('order'));
}

    public function loginAction(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Remove the remember_token from the user object if remember me was not selected
            if (!$request->boolean('remember')) {
                Auth::user()->setRememberToken(null);
                Auth::user()->save();
            }

            return redirect()->route('cashierDashboard'); // Change this to your desired route for cashier dashboard
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }



    public function cashierDailySalesReport()
    {
        // Get today's date
        $today = now()->toDateString();

        // Every order paid today, whether or not the kitchen has marked it Done yet
        $customerOrders = Order::revenue()
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        $totalCash = Order::revenue()
            ->where('payment_method', 'Cash')
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        $totalGCash = Order::revenue()
            ->where('payment_method', 'Gcash')
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        // Total every payment method, so a method other than Cash/GCash is never dropped
        $totalSales = Order::revenue()
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        // Snapshot today's figures for the admin report
        DailySalesReport::updateOrCreate(
            ['report_date' => $today],
            [
                'total_cash' => $totalCash,
                'total_gcash' => $totalGCash,
                'total_sales' => $totalSales,
            ]
        );

        return view('cashier.daily_sales_report', [
            'totalCash' => $totalCash,
            'totalGCash' => $totalGCash,
            'totalSales' => $totalSales,
            'customerOrders' => $customerOrders,
        ]);
    }
}






