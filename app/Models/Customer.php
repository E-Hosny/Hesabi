<?php

// app/Models/Customer.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers'; 

    protected $fillable = [
        'name',
        'email',
        'phone',
        'currency_type',
        'user_id'
    ];


    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function balance()
    {
        $credit = $this->transactions()->where('type', 'credit')->sum('amount');
        $debit = $this->transactions()->where('type', 'debit')->sum('amount');
        return $credit - $debit;
    }

}

