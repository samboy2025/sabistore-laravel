<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'last_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'last_updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the wallet
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the wallet transactions
     */
    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class, 'user_id', 'user_id');
    }

    /**
     * Credit the wallet with the specified amount
     */
    public function credit($amount, $type, $description, $reference = null, $relatedOrderId = null)
    {
        return DB::transaction(function () use ($amount, $type, $description, $reference, $relatedOrderId) {
            // Update wallet balance
            $this->increment('balance', $amount);
            $this->update(['last_updated_at' => now()]);
            
            // Create transaction record
            return WalletTransaction::create([
                'user_id' => $this->user_id,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $this->fresh()->balance,
                'reference' => $reference,
                'description' => $description,
                'status' => 'completed',
                'related_order_id' => $relatedOrderId,
            ]);
        });
    }

    /**
     * Debit the wallet with the specified amount
     */
    public function debit($amount, $type, $description, $reference = null, $relatedOrderId = null)
    {
        return DB::transaction(function () use ($amount, $type, $description, $reference, $relatedOrderId) {
            // Check if sufficient balance
            if ($this->balance < $amount) {
                throw new \Exception('Insufficient wallet balance');
            }
            
            // Update wallet balance
            $this->decrement('balance', $amount);
            $this->update(['last_updated_at' => now()]);
            
            // Create transaction record
            return WalletTransaction::create([
                'user_id' => $this->user_id,
                'type' => $type,
                'amount' => -$amount, // Negative for debit
                'balance_after' => $this->fresh()->balance,
                'reference' => $reference,
                'description' => $description,
                'status' => 'completed',
                'related_order_id' => $relatedOrderId,
            ]);
        });
    }

    /**
     * Check if wallet has sufficient balance
     */
    public function hasSufficientBalance($amount)
    {
        return $this->balance >= $amount;
    }

    /**
     * Get formatted balance
     */
    public function getFormattedBalanceAttribute()
    {
        return '₦' . number_format($this->balance, 2);
    }
}