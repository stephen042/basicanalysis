<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'txn_id',
        'user',
        'amount',
        'to_deduct',
        'status',
        'payment_mode',
        'paydetails',
        'method_id',
        'asset',
        'currency',
        'network',
        'charges',
        'submission_key',
    ];

    public function duser(){
    	return $this->belongsTo(User::class , 'user');
    }

    public function method()
    {
        return $this->belongsTo(Wdmethod::class, 'method_id');
    }
}
