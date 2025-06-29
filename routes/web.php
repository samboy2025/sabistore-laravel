<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminShopController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminCourseController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\Vendor\ShopController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Vendor\ResellerLinkController;
use App\Http\Controllers\Vendor\BadgeController;
use App\Http\Controllers\Buyer\BuyerDashboardController;
use App\Http\Controllers\Buyer\OrderController as BuyerOrderController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\VendorDirectoryController;
use App\Http\Controllers\Public\CourseController;
use App\Http\Controllers\Public\ShopPageController;
use App\Http\Controllers\Payment\MembershipPaymentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\Admin\AdminWalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (Main Domain)
|--------------------------------------------------------------------------
*/

// Homepage and public pages
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/vendors', [VendorDirectoryController::class, 'index'])->name('vendors.directory');
Route::get('/vendors/{shop:slug}', [VendorDirectoryController::class, 'show'])->name('vendors.show');

// Learning Center (Public)
Route::prefix('courses')->name('courses.')->group(function () {
    Route::get('/', [CourseController::class, 'index'])->name('index');
    Route::get('/{course:slug}', [CourseController::class, 'show'])->name('show');
});

// Authentication Routes
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);

// Simple profile route (temporary)
Route::middleware(['auth'])->get('/profile', function() {
    return redirect()->route('home')->with('info', 'Profile functionality coming soon');
})->name('profile.edit');

/*
|--------------------------------------------------------------------------
| Protected Routes (Main Domain)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    
    // Follow/Unfollow Routes
    Route::post('/vendors/{vendor}/follow', [FollowController::class, 'follow'])->name('vendors.follow');
    Route::delete('/vendors/{vendor}/unfollow', [FollowController::class, 'unfollow'])->name('vendors.unfollow');
    Route::get('/vendors/{vendor}/followers', [FollowController::class, 'followers'])->name('vendors.followers');
    Route::get('/following', [FollowController::class, 'following'])->name('following');
    
    // Membership Payment Routes
    Route::prefix('membership')->name('membership.')->group(function () {
        Route::get('/payment', [MembershipPaymentController::class, 'show'])->name('payment');
        Route::post('/payment', [MembershipPaymentController::class, 'process'])->name('process');
        Route::get('/callback', [MembershipPaymentController::class, 'callback'])->name('callback');
        
        // Temporary bypass for testing (remove in production)
        Route::get('/bypass', function() {
            $user = auth()->user();
            if ($user && $user->isVendor()) {
                $user->update(['membership_active' => true, 'membership_paid_at' => now()]);
                return redirect()->route('vendor.dashboard')->with('success', 'Membership activated for testing!');
            }
            return redirect()->route('login');
        })->name('bypass');
    });

    // Admin Routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/analytics', [AdminDashboardController::class, 'analytics'])->name('analytics');
        
        Route::resource('users', AdminUserController::class);
        Route::resource('shops', AdminShopController::class);
        Route::resource('payments', AdminPaymentController::class);
        Route::resource('courses', AdminCourseController::class);
        
        // Settings routes
        Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings');
        Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
        Route::get('/settings/payments', [AdminSettingsController::class, 'payments'])->name('settings.payments');
        Route::put('/settings/payments', [AdminSettingsController::class, 'updatePayments'])->name('settings.update-payments');
        
        Route::post('/shops/{shop}/toggle-status', [AdminShopController::class, 'toggleStatus'])->name('shops.toggle-status');

        // Admin Wallet Management Routes
        Route::prefix('wallets')->name('wallets.')->group(function () {
            Route::get('/', [AdminWalletController::class, 'index'])->name('index');
            Route::get('/users', [AdminWalletController::class, 'users'])->name('users');
            Route::get('/users/{user}', [AdminWalletController::class, 'show'])->name('show');
            Route::post('/users/{user}/adjust', [AdminWalletController::class, 'adjust'])->name('adjust');
            Route::post('/bulk-adjust', [AdminWalletController::class, 'bulkAdjust'])->name('bulk-adjust');
            Route::get('/export', [AdminWalletController::class, 'export'])->name('export');
        });
    });

    // Vendor Routes
    Route::middleware(['role:vendor'])->prefix('vendor')->name('vendor.')->group(function () {
        Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('dashboard');
        
        // Shop Management
        Route::get('/shop/setup', [ShopController::class, 'setup'])->name('shop.setup');
        Route::post('/shop/complete-setup', [ShopController::class, 'completeSetup'])->name('shop.complete-setup');
        Route::resource('shop', ShopController::class)->except(['index', 'show']);
        
        // Product Management (requires membership)
        Route::middleware(['membership'])->group(function () {
            Route::resource('products', ProductController::class);
        });
        
        // Order Management
        Route::resource('orders', VendorOrderController::class)->only(['index', 'show', 'update']);
        Route::post('/orders/{order}/mark-shipped', [VendorOrderController::class, 'markShipped'])->name('orders.mark-shipped');
        Route::post('/orders/{order}/mark-delivered', [VendorOrderController::class, 'markDelivered'])->name('orders.mark-delivered');
        
        // Reseller Links (requires membership)
        Route::middleware(['membership'])->group(function () {
            Route::resource('reseller-links', ResellerLinkController::class);
        });
        
        // Learning Center (Vendor Access)
        Route::get('/learning', [CourseController::class, 'vendorIndex'])->name('learning.index');
        Route::post('/learning/{course}/complete', [CourseController::class, 'markComplete'])->name('learning.complete');
        
        // Shop Preview
        Route::get('/shop/preview', [ShopController::class, 'preview'])->name('shop.preview');
        
        // Badge Status
        Route::get('/badge', [BadgeController::class, 'index'])->name('badge.index');
        
        // Followers Management
        Route::get('/followers', function() {
            return app(\App\Http\Controllers\FollowController::class)->followers(auth()->user());
        })->name('followers');

        // Profile Management
        Route::get('/profile/edit', [App\Http\Controllers\Vendor\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [App\Http\Controllers\Vendor\ProfileController::class, 'update'])->name('profile.update');

        // Wallet Management
        Route::get('/wallet', [App\Http\Controllers\Vendor\WalletController::class, 'index'])->name('wallet.index');
        Route::post('/wallet/fund', [App\Http\Controllers\Vendor\WalletController::class, 'fund'])->name('wallet.fund');
        Route::get('/wallet/callback', [App\Http\Controllers\Vendor\WalletController::class, 'callback'])->name('wallet.callback');
    });

    // Buyer Routes
    Route::middleware(['role:buyer'])->prefix('buyer')->name('buyer.')->group(function () {
        Route::get('/dashboard', [BuyerDashboardController::class, 'index'])->name('dashboard');
        Route::resource('orders', BuyerOrderController::class)->only(['index', 'show']);
    });
});

/*
|--------------------------------------------------------------------------
| API Routes for Wallet System
|--------------------------------------------------------------------------
*/

Route::prefix('api')->middleware(['auth'])->group(function () {
    Route::get('/wallet', [App\Http\Controllers\Api\WalletApiController::class, 'getWallet'])->name('api.wallet.get');
    Route::post('/wallet/fund', [App\Http\Controllers\Api\WalletApiController::class, 'fundWallet'])->name('api.wallet.fund');
    Route::post('/wallet/fund/callback', [App\Http\Controllers\Api\WalletApiController::class, 'walletFundingCallback'])->name('api.wallet.callback');
    Route::post('/products/{product}/buy', [App\Http\Controllers\Api\WalletApiController::class, 'buyProduct'])->name('api.products.buy');
    Route::get('/reseller-link/{code}', [App\Http\Controllers\Api\WalletApiController::class, 'trackResellerLink'])->name('api.reseller.track');
});

// Wallet web routes
Route::middleware(['auth'])->group(function () {
    Route::get('/wallet', [App\Http\Controllers\WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/fund', [App\Http\Controllers\WalletController::class, 'fund'])->name('wallet.fund');
});

/*
|--------------------------------------------------------------------------
| Subdomain Routes (Vendor Shops) - Commented out temporarily
|--------------------------------------------------------------------------
*/

/*
Route::domain('{shop}.'.env('APP_DOMAIN', 'localhost'))->middleware(['tenant'])->group(function () {
    
    // Shop Homepage
    Route::get('/', [ShopPageController::class, 'index'])->name('shop.home');
    
    // Products
    Route::get('/products', [ShopPageController::class, 'products'])->name('shop.products');
    Route::get('/products/{product}', [ShopPageController::class, 'product'])->name('shop.product');
    
    // Reseller tracking
    Route::get('/r/{code}', [ShopPageController::class, 'resellerRedirect'])->name('shop.reseller');
    
    // Order placement (if not using WhatsApp)
    Route::post('/order/{product}', [ShopPageController::class, 'placeOrder'])->name('shop.place-order')->middleware('auth');
    
});
*/
