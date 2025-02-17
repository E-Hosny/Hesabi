<?php

// app/Http/Controllers/CustomerController.php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;


class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::all();
        return Inertia::render('Customers/Index', [
            'customers' => $customers
        ]);
    }

    public function create()
    {
        return Inertia::render('Customers/Create');
    }

    public function store(Request $request)
    {
        // dd("here");
        $request->validate([
            'name' => 'required|string|max:255',
            // 'email' => 'required|email|unique:customers,email',
            // 'phone' => 'nullable|string|max:15',
        ]);

        Customer::create($request->all());

        return redirect()->route('customers.index')->with('success', 'تم إضافة العميل بنجاح');
    }

    public function edit(Customer $customer)
    {
        return Inertia::render('Customers/Edit', [
            'customer' => $customer
        ]);
    }

    public function update(Request $request, Customer $customer)
        {
            // dd($customer->toArray());
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $customer->update([
                'name' => $request->name,
            ]);

            return redirect()->back()->with('success', 'تم تعديل الاسم بنجاح');
        }


        public function show(Customer $customer)
        {
            $transactions = $customer->transactions()
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'amount' => $transaction->amount,
                        'details' => $transaction->details,
                        'created_at' => Carbon::parse($transaction->created_at)->format('d/m/Y H:i'),
                        'remaining_balance' => $transaction->remaining_balance,
                        'type' =>$transaction->type,
                    ];
                });
        
            return Inertia::render('Customers/Show', [
                'customer' => $customer,
                'transactions' => $transactions
            ]);
        }
        



    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'تم حذف العميل بنجاح');
    }
}
