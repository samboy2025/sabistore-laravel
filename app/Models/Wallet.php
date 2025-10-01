<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Wallet
 *
 * Represents a user's wallet, storing their balance and handling transactions.
 *
 * @package App\Models
 * @property int $id
 * @property int $user_id The ID of the user who owns the wallet.
 * @property float $balance The current balance of the wallet.
 * @property \Illuminate\Support\Carbon|null $last_updated_at Timestamp of the last balance update.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read User $user The user who owns the wallet.
 * @property-read \Illuminate\Database\Eloquent\Collection|WalletTransaction[] $transactions The transactions for this wallet.
 * @property-read string $formatted_balance The balance formatted as a currency string.
 */
class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'last_updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'last_updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the wallet.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transactions associated with this wallet.
     *
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'user_id', 'user_id');
    }

    /**
     * Credit the wallet with a specified amount and record the transaction.
     *
     * @param float $amount The amount to credit.
     * @param string $type The type of transaction (e.g., 'funding', 'commission').
     * @param string $description A description of the transaction.
     * @param string|null $reference An external reference for the transaction.
     * @param int|null $relatedOrderId The ID of a related order, if applicable.
     * @return WalletTransaction The created transaction record.
     */
    public function credit(float $amount, string $type, string $description, string $reference = null, int $relatedOrderId = null): WalletTransaction
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
     * Debit the wallet with a specified amount and record the transaction.
     *
     * @param float $amount The amount to debit.
     * @param string $type The type of transaction (e.g., 'purchase', 'withdrawal').
     * @param string $description A description of the transaction.
     * @param string|null $reference An external reference for the transaction.
     * @param int|null $relatedOrderId The ID of a related order, if applicable.
     * @return WalletTransaction The created transaction record.
     * @throws \Exception if the balance is insufficient.
     */
    public function debit(float $amount, string $type, string $description, string $reference = null, int $relatedOrderId = null): WalletTransaction
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
     * Check if the wallet has a sufficient balance for a given amount.
     *
     * @param float $amount The amount to check against.
     * @return bool
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * Get the balance formatted as a currency string.
     *
     * @return string
     */
    public function getFormattedBalanceAttribute(): string
    {
        return '₦' . number_format($this->balance, 2);
    }
}