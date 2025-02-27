<?php

// app/Http/Controllers/CustomerController.php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function store(Request $request)
    {
        // dd($request);
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:credit,debit',
            'details' => 'nullable|string'
        ]);
    
        Transaction::create([
            'customer_id' => $request->customer_id,
            'amount' => $request->amount,
            'type' => $request->type,
            'details' => $request->details
        ]);
    
        return redirect()->back()->with('success', 'تمت إضافة المعاملة بنجاح!');
    }



    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
    
        $request->validate([
            'amount' => 'required|numeric',
            'type' => 'required|in:credit,debit',
            'details' => 'nullable|string',
        ]);
    
        $transaction->update([
            'amount' => $request->amount,
            'type' => $request->type,
            'details' => $request->details,
        ]);
    
        return redirect()->route('customers.show', ['customer' => $transaction->customer_id])
                         ->with('success', 'تم التحديث بنجاح');
    }


    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();

        return redirect()->back()->with('success', 'تم حذف المعاملة بنجاح.');
    }
    

   
}
