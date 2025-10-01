<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class User
 *
 * Represents a user of the application, who can be an admin, vendor, or buyer.
 *
 * @package App\Models
 * @property int $id
 * @property string $name The user's full name.
 * @property string $email The user's unique email address.
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password The user's hashed password.
 * @property string $role The user's role (admin, vendor, buyer).
 * @property string|null $phone The user's phone number.
 * @property string|null $whatsapp_number The user's WhatsApp number (especially for vendors).
 * @property string|null $bvn_nin The user's Bank Verification Number or National Identity Number (for vendors).
 * @property bool $membership_active Whether the vendor's membership is currently active.
 * @property \Illuminate\Support\Carbon|null $membership_paid_at Timestamp when the membership was last paid.
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read Shop|null $shop The shop associated with the user (if they are a vendor).
 * @property-read \Illuminate\Database\Eloquent\Collection|Order[] $orders The orders placed by the user (if they are a buyer).
 * @property-read \Illuminate\Database\Eloquent\Collection|Payment[] $payments The payments made by the user.
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $following The vendors this user is following.
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $followers The users who are following this user (if they are a vendor).
 * @property-read int $followers_count The number of followers this user has.
 * @property-read int $following_count The number of vendors this user is following.
 * @property-read Wallet|null $wallet The user's wallet.
 * @property-read \Illuminate\Database\Eloquent\Collection|WalletTransaction[] $walletTransactions The user's wallet transactions.
 * @property-read \Illuminate\Database\Eloquent\Collection|Certificate[] $certificates The certificates awarded to this user.
 * @property-read \Illuminate\Database\Eloquent\Collection|CourseEnrollment[] $courseEnrollments The user's course enrollments.
 * @property-read \Illuminate\Database\Eloquent\Collection|UserLogin[] $loginHistory The user's login history.
 * @property-read \Illuminate\Database\Eloquent\Collection|ResellerCommission[] $resellerCommissions Commissions earned by this user as a reseller.
 * @property-read \Illuminate\Database\Eloquent\Collection|ResellerCommission[] $vendorCommissions Commissions paid by this user as a vendor.
 * @property-read float $wallet_balance The current balance of the user's wallet.
 * @property-read string $formatted_wallet_balance The wallet balance formatted as a currency string.
 */
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
     * Determine if the user is a vendor.
     *
     * @return bool
     */
    public function isVendor(): bool
    {
        return $this->role === 'vendor';
    }

    /**
     * Determine if the user is a buyer.
     *
     * @return bool
     */
    public function isBuyer(): bool
    {
        return $this->role === 'buyer';
    }

    /**
     * Determine if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Get the shop associated with the user (if they are a vendor).
     *
     * @return HasOne
     */
    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class, 'vendor_id');
    }

    /**
     * Get the orders placed by the user (if they are a buyer).
     *
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    /**
     * Get the payments made by the user.
     *
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the vendors that this user is following.
     *
     * @return BelongsToMany
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'vendor_id')
                    ->withTimestamps();
    }

    /**
     * Get the users who are following this user (if they are a vendor).
     *
     * @return BelongsToMany
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'vendor_id', 'follower_id')
                    ->withTimestamps();
    }

    /**
     * Check if the current user is following a specific vendor.
     *
     * @param User $vendor The vendor to check.
     * @return bool
     */
    public function isFollowing(User $vendor): bool
    {
        return $this->following()->where('vendor_id', $vendor->id)->exists();
    }

    /**
     * Follow a vendor.
     *
     * @param User $vendor The vendor to follow.
     * @return void
     */
    public function follow(User $vendor): void
    {
        if (!$this->isFollowing($vendor) && $vendor->isVendor() && $this->id !== $vendor->id) {
            $this->following()->attach($vendor->id);
        }
    }

    /**
     * Unfollow a vendor.
     *
     * @param User $vendor The vendor to unfollow.
     * @return void
     */
    public function unfollow(User $vendor): void
    {
        $this->following()->detach($vendor->id);
    }

    /**
     * Get the number of followers for this vendor.
     *
     * @return int
     */
    public function getFollowersCountAttribute(): int
    {
        return $this->followers()->count();
    }

    /**
     * Get the number of vendors this user is following.
     *
     * @return int
     */
    public function getFollowingCountAttribute(): int
    {
        return $this->following()->count();
    }

    /**
     * Get the user's wallet.
     *
     * @return HasOne
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Get the user's wallet transactions.
     *
     * @return HasMany
     */
    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Get the certificates awarded to this user.
     *
     * @return HasMany
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Get the user's course enrollments.
     *
     * @return HasMany
     */
    public function courseEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /**
     * Get the user's login history.
     *
     * @return HasMany
     */
    public function loginHistory(): HasMany
    {
        return $this->hasMany(UserLogin::class);
    }

    /**
     * Get the commissions earned by this user as a reseller.
     *
     * @return HasMany
     */
    public function resellerCommissions(): HasMany
    {
        return $this->hasMany(ResellerCommission::class, 'reseller_id');
    }

    /**
     * Get the commissions paid out by this user as a vendor.
     *
     * @return HasMany
     */
    public function vendorCommissions(): HasMany
    {
        return $this->hasMany(ResellerCommission::class, 'vendor_id');
    }

    /**
     * Get the user's wallet, creating one if it doesn't exist.
     *
     * @return Wallet
     */
    public function getOrCreateWallet(): Wallet
    {
        return $this->wallet ?? $this->wallet()->create([
            'balance' => 0.00,
            'last_updated_at' => now(),
        ]);
    }

    /**
     * Get the current balance of the user's wallet.
     *
     * @return float
     */
    public function getWalletBalanceAttribute(): float
    {
        $wallet = $this->getOrCreateWallet();
        return $wallet->balance;
    }

    /**
     * Get the wallet balance formatted as a currency string.
     *
     * @return string
     */
    public function getFormattedWalletBalanceAttribute(): string
    {
        return '₦' . number_format($this->wallet_balance, 2);
    }

    /**
     * Check if the user has a sufficient wallet balance for a given amount.
     *
     * @param float $amount The amount to check against.
     * @return bool
     */
    public function hasSufficientWalletBalance(float $amount): bool
    {
        return $this->wallet_balance >= $amount;
    }
}
