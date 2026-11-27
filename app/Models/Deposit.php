<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    public function duser(){
    	return $this->belongsTo('App\Models\User', 'user');
    }

    public function dplan(){
        return $this->belongsTo('App\Models\Plans', 'plan');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(Wdmethod::class, 'payment_method_id');
    }

    public function compoundContribution()
    {
        return $this->hasOne(CompoundInvestmentContribution::class, 'deposit_id');
    }
}
