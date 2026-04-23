<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignalTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'signal_id',
        'amount',
        'transaction_id',
        'status',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that made the purchase.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the signal plan purchased.
     */
    public function signal(): BelongsTo
    {
        return $this->belongsTo(Signal::class);
    }
}
