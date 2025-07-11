<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'whatsapp_number',
        'bvn_nin',
        'membership_active',
        'membership_paid_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'membership_paid_at' => 'datetime',
            'password' => 'hashed',
            'membership_active' => 'boolean',
        ];
    }

    /**
     * Determine if the user is a vendor
     */
    public function isVendor(): bool
    {
        return $this->role === 'vendor';
    }

    /**
     * Determine if the user is a buyer
     */
    public function isBuyer(): bool
    {
        return $this->role === 'buyer';
    }

    /**
     * Determine if the user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Get the shop associated with the vendor
     */
    public function shop()
    {
        return $this->hasOne(Shop::class, 'vendor_id');
    }

    /**
     * Get the orders made by the buyer
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    /**
     * Get the payments made by the user
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Vendors that this user is following
     */
    public function following()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'vendor_id')
                    ->withTimestamps();
    }

    /**
     * Users who are following this vendor
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'vendor_id', 'follower_id')
                    ->withTimestamps();
    }

    /**
     * Check if this user is following a specific vendor
     */
    public function isFollowing(User $vendor): bool
    {
        return $this->following()->where('vendor_id', $vendor->id)->exists();
    }

    /**
     * Follow a vendor
     */
    public function follow(User $vendor): void
    {
        if (!$this->isFollowing($vendor) && $vendor->isVendor() && $this->id !== $vendor->id) {
            $this->following()->attach($vendor->id);
        }
    }

    /**
     * Unfollow a vendor
     */
    public function unfollow(User $vendor): void
    {
        $this->following()->detach($vendor->id);
    }

    /**
     * Get follower count for this vendor
     */
    public function getFollowersCountAttribute(): int
    {
        return $this->followers()->count();
    }

    /**
     * Get following count for this user
     */
    public function getFollowingCountAttribute(): int
    {
        return $this->following()->count();
    }

    /**
     * Get the user's wallet
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Get wallet transactions
     */
    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Get user's certificates
     */
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Get user's course enrollments
     */
    public function courseEnrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /**
     * Get user's login history
     */
    public function loginHistory()
    {
        return $this->hasMany(UserLogin::class);
    }

    /**
     * Get reseller commissions earned by this user
     */
    public function resellerCommissions()
    {
        return $this->hasMany(ResellerCommission::class, 'reseller_id');
    }

    /**
     * Get commissions paid by this vendor
     */
    public function vendorCommissions()
    {
        return $this->hasMany(ResellerCommission::class, 'vendor_id');
    }

    /**
     * Get or create wallet for user
     */
    public function getOrCreateWallet()
    {
        return $this->wallet ?? $this->wallet()->create([
            'balance' => 0.00,
            'last_updated_at' => now(),
        ]);
    }

    /**
     * Get wallet balance
     */
    public function getWalletBalanceAttribute()
    {
        $wallet = $this->getOrCreateWallet();
        return $wallet->balance;
    }

    /**
     * Get formatted wallet balance
     */
    public function getFormattedWalletBalanceAttribute()
    {
        return '₦' . number_format($this->wallet_balance, 2);
    }

    /**
     * Check if user has sufficient wallet balance
     */
    public function hasSufficientWalletBalance($amount)
    {
        return $this->wallet_balance >= $amount;
    }
}
