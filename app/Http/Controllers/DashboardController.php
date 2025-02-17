<?php

// app/Http/Controllers/CustomerController.php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {

        $customers = Customer::all();

        return Inertia::render('Dashboard', [
            'customers' => $customers
        ]);
    }

   
}
