<?php

namespace App\Models;

use App\Models\SignalTransaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Signal extends Model
{
    protected $fillable = ['name', 'duration', 'amount', 'status'];

    /**
     * Get all transactions for this signal plan.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(SignalTransaction::class);
    }
}
