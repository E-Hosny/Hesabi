<?php

// app/Http/Controllers/CustomerController.php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;


class CustomerController extends Controller
{
    // public function index()
    // {
    //     $customers = Customer::with('transactions')->get()->map(function ($customer) {
    //         $totalBalance = $customer->transactions->reduce(function ($carry, $transaction) {
    //             return $transaction->type === 'credit' 
    //                 ? $carry + $transaction->amount 
    //                 : $carry - $transaction->amount;
    //         }, 0);
    
    //         return [
    //             'id' => $customer->id,
    //             'name' => $customer->name,
    //             'final_balance' => $totalBalance,
    //         ];
    //     });
    
    //     return Inertia::render('Customers/Index', [
    //         'customers' => $customers,
    //     ]);
    // }

    public function index(Request $request)
    {
        $currencyType = $request->query('currency_type', 'local');
    
        $customers = Customer::with('transactions')
            ->where('user_id', auth()->id()) // جلب العملاء الخاصين بالمستخدم الحالي فقط
            ->where('currency_type', $currencyType)
            ->get()
            ->map(function ($customer) {
                $totalBalance = $customer->transactions->reduce(function ($carry, $transaction) {
                    return $transaction->type === 'credit' 
                        ? $carry + $transaction->amount 
                        : $carry - $transaction->amount;
                }, 0);
    
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'final_balance' => $totalBalance, 
                ];
            });
    
        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'selectedCurrency' => $currencyType,
        ]);
    }
    
    

    

    public function create()
    {
        return Inertia::render('Customers/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'currency_type' => 'required|in:local,usd,sar',
            'phone' => 'nullable|string|max:15',
        ]);
    
        Customer::create([
            'name' => $request->name,
            'currency_type' => $request->currency_type,
            'phone' => $request->phone,
            'user_id' => auth()->id(), // تعيين المستخدم الحالي
        ]);
    
        return back()->with('success', 'تم إضافة العميل بنجاح');
    }
    

    public function edit(Customer $customer)
    {
        return Inertia::render('Customers/Edit', [
            'customer' => $customer
        ]);
    }

    public function update(Request $request, Customer $customer)
{
    if ($customer->user_id !== auth()->id()) {
        abort(403, 'ليس لديك صلاحية لتعديل هذا العميل');
    }

    $request->validate([
        'name' => 'required|string|max:255',
        'phone' => 'nullable|string|max:15',
    ]);

    $customer->update([
        'name' => $request->name,
        'phone' => $request->phone
    ]);

    return redirect()->back()->with('success', 'تم تعديل الاسم بنجاح');
}

        public function show(Customer $customer)
        {
            $transactions = $customer->transactions()
                ->orderBy('created_at', 'asc') 
                ->get();
        
            $balance = 0;
        
            $transactions = $transactions->map(function ($transaction) use (&$balance) {
                if ($transaction->type === 'credit') {
                    $balance += $transaction->amount;
                } else {
                    $balance -= $transaction->amount;
                }
        
                return [
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'details' => $transaction->details,
                    'created_at' => Carbon::parse($transaction->created_at)->format('d/m/Y H:i'),
                    'remaining_balance' => $balance,
                    'type' => $transaction->type,
                ];
            });
        
            return Inertia::render('Customers/Show', [
                'customer' => $customer,
                'transactions' => $transactions
            ]);
        }
        
        



        public function destroy(Customer $customer)
        {
            if ($customer->user_id !== auth()->id()) {
                abort(403, 'ليس لديك صلاحية لحذف هذا العميل');
            }
        
            $customer->delete();
            return back()->with('success', 'تم حذف العميل بنجاح');
        }
        
}
