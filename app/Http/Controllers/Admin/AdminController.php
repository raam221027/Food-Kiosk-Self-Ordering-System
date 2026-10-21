<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\Product;
use App\Models\CustomizedProduct;
use App\Models\Order;
use App\Models\AddOns;
use App\Models\Payment;
use App\Models\OrderItem;
use App\Models\CustomerOrder;
use App\Models\DailySalesReport;
use Illuminate\Support\Carbon;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::latest()->paginate(5);
        return view('admin.index', compact('users'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::all();
        return view('admin.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $permissions = Permission::all();
        try {
            // Validate the request data
            $request->validate([
                'name' => ['required', 'min:3'],
                'email' => ['email', 'required', 'unique:users'],
                'password' => ['required', 'confirmed', 'min:6'],
                'role' => ['required', Rule::in(['Cashier', 'Kitchen'])],
            ]);

            // Create a new user instance
            $user = new User([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            // Save the user to the database
            $user->save();

            // Assign the role to the user based on the selected value
            $user->assignRole($request->role);

            // Redirect to the list of users with a success message
            return redirect()->route('admin.index')->with(['success' => 'User created successfully', 'success_modal' => true]);
        } catch (ValidationException $e) {
            // Handle validation errors
            return redirect()->back()->withErrors($e->errors());
        } catch (\Exception $e) {
            // Handle other exceptions
            return redirect()->back()->with('error', 'User not registered. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::find($id);
        $permissions = Permission::all();
        return view('admin.edit', compact('user', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validate the request data
        $request->validate([
            'name' => ['required', 'min:3'],
            'email' => ['email', 'required'],
            'role' => ['required', Rule::in(['Cashier', 'Kitchen'])],
        ]);

        try {
            $user = User::find($id);
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
            ]);

            // Update the role for the user based on the selected value
            $user->syncRoles($request->role);

            // Redirect to the list of users with a success message
            return redirect()->route('admin.index')->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            // Redirect back with an error message if something goes wrong
            return redirect()->back()->with('error', 'User not updated. Please try again.');
        }
    }

    /**
     * Disable the specified user.
     */
    public function disableUser(string $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->is_disabled = true;
            $user->save();
            
            Session::flash('success', 'User has been disabled successfully');
            return redirect()->route('admin.index');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'User not disabled');
        }
    }

    /**
     * Enable the specified user.
     */ 
    public function enableUser(string $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->is_disabled = false;
            $user->save();
            
            Session::flash('success', 'User has been enabled successfully');
            return redirect()->route('admin.index');
        } catch (\Exception $e) {
            Session::flash('error', 'User not enabled');
            return redirect()->back();
        }
    }

    /**
     * Toggle the user status via AJAX.
     */
    public function toggleStatus(Request $request)
    {
        $user = User::find($request->id);

        if ($user) {
            $user->update(['is_disabled' => !$user->is_disabled]);
            return response()->json(['is_disabled' => $user->is_disabled], 200);
        }

        return response()->json(['error' => 'User not found'], 404);
    }

    /**
     * Show roles assignment form.
     */
    public function assignRoles(string $id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        return view('admin.assign_roles', compact('user', 'roles'));
    }

    /**
     * Handle the role assignment form submission.
     */
    public function storeRoles(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'roles' => 'array', // Make sure roles is an array
        ]);

        $roles = $request->input('roles', []);
        $user->syncRoles($roles);

        // Role assignment successful message
        Session::flash('success', "Roles have been assigned successfully");

        // Redirect to the list of users
        return redirect()->route('admin.index');
    }

    /**
     * Display daily sales report.
     */
    public function adminDailySalesReport(Request $request)
    {
        $months = [
            'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
            'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
            'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
        ];

        $dateFilter = $request->input('date_filter', 'this_month');
        $year = (int) $request->input('year', now()->year);

        // Resolve the filter to an explicit date range. A month on its own is ambiguous:
        // filtering on the month number alone would mix that month from every year together.
        if ($dateFilter == 'today') {
            $start = now()->startOfDay();
            $end = now()->endOfDay();
        } elseif (isset($months[$dateFilter])) {
            $start = Carbon::create($year, $months[$dateFilter], 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
        } else {
            $dateFilter = 'this_month';
            $start = Carbon::create($year, now()->month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
        }

        // Build the report from the orders table, which is the source of truth.
        // daily_sales_reports is only a snapshot and has no row for any day nobody
        // happened to open a report on, so reading it directly loses whole months of sales.
        $dailyTotals = Order::revenue()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as report_date')
            ->selectRaw("SUM(CASE WHEN payment_method = 'Cash' THEN total_amount ELSE 0 END) as total_cash")
            ->selectRaw("SUM(CASE WHEN payment_method = 'Gcash' THEN total_amount ELSE 0 END) as total_gcash")
            ->selectRaw('SUM(total_amount) as total_sales')
            ->groupBy('report_date')
            ->orderBy('report_date')
            ->get();

        // Bring the stored snapshots back in step with the figures just calculated
        $reports = $dailyTotals->map(function ($totals) {
            return DailySalesReport::updateOrCreate(
                ['report_date' => $totals->report_date],
                [
                    'total_cash' => $totals->total_cash,
                    'total_gcash' => $totals->total_gcash,
                    'total_sales' => $totals->total_sales,
                ]
            );
        });

        // Years that actually have sales, so the filter cannot select an empty year
        $years = Order::revenue()
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->pluck('year')
            ->push(now()->year)
            ->unique()
            ->sortDesc()
            ->values();

        $totalCollectablesForMonth = $reports->sum('total_cash');
        $totalReceivablesForMonth = $reports->sum('total_gcash');
        $totalSalesForMonth = $reports->sum('total_sales');

        // Display the results
        return view('admin.daily_sales_report', compact(
            'reports',
            'dateFilter',
            'year',
            'years',
            'totalSalesForMonth',
            'totalCollectablesForMonth',
            'totalReceivablesForMonth'
        ));
    }
}
