<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Certificate;
use App\Models\VendorFollow;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BuyerDashboardController extends Controller
{
    /**
     * Display the buyer dashboard
     */
    public function index(): View
    {
        $user = Auth::user();

        // Buyer statistics
        $stats = [
            'total_orders' => $user->orders()->count(),
            'pending_orders' => $user->orders()->where('status', 'pending')->count(),
            'completed_orders' => $user->orders()->where('status', 'delivered')->count(),
            'total_spent' => $user->orders()
                ->where('payment_status', 'paid')
                ->sum('total_price'),
            'digital_products' => $user->orders()
                ->whereHas('product', fn($q) => $q->where('type', 'digital'))
                ->where('payment_status', 'paid')
                ->count(),
            'wallet_balance' => $user->wallet_balance,
            'enrolled_courses' => $user->courseEnrollments()->count(),
            'completed_courses' => $user->courseEnrollments()->completed()->count(),
            'certificates_earned' => $user->certificates()->count(),
            'vendors_following' => $user->followedVendors()->count(),
            'resale_earnings' => $user->walletTransactions()
                ->where('type', 'commission')
                ->sum('amount'),
        ];

        // Recent orders
        $recentOrders = $user->orders()
            ->with(['product', 'shop', 'product.shop'])
            ->latest()
            ->take(10)
            ->get();

        // Digital products available for download
        $digitalProducts = $user->orders()
            ->with(['product'])
            ->whereHas('product', function($query) {
                $query->where('type', 'digital');
            })
            ->where('payment_status', 'paid')
            ->get()
            ->pluck('product')
            ->unique('id');

        // Recently viewed products (this would need session storage)
        $recentlyViewed = collect(); // Implement session-based tracking

        // Recommended products based on order history
        $recommendedProducts = $this->getRecommendedProducts($user);

        // Favorite vendors (most ordered from)
        $favoriteVendors = $user->orders()
            ->with(['shop'])
            ->selectRaw('shop_id, COUNT(*) as order_count')
            ->groupBy('shop_id')
            ->orderByDesc('order_count')
            ->take(5)
            ->get()
            ->pluck('shop')
            ->filter();

        // Recent course enrollments
        $recentCourses = $user->courseEnrollments()
            ->with(['course'])
            ->latest()
            ->take(5)
            ->get();

        // Recent certificates
        $recentCertificates = $user->certificates()
            ->with(['course'])
            ->latest()
            ->take(3)
            ->get();

        // Followed vendors
        $followedVendors = $user->followedVendors()
            ->with(['vendor', 'vendor.shop'])
            ->latest()
            ->take(5)
            ->get();

        // Recent wallet transactions
        $recentTransactions = $user->walletTransactions()
            ->latest()
            ->take(10)
            ->get();

        return view('buyer.dashboard', compact(
            'user',
            'stats',
            'recentOrders',
            'digitalProducts',
            'recentlyViewed',
            'recommendedProducts',
            'favoriteVendors',
            'recentCourses',
            'recentCertificates',
            'followedVendors',
            'recentTransactions'
        ));
    }

    /**
     * Show order history
     */
    public function orders(): View
    {
        $user = Auth::user();

        $orders = $user->orders()
            ->with(['product', 'shop', 'product.shop'])
            ->latest()
            ->paginate(20);

        return view('buyer.orders', compact('orders'));
    }

    /**
     * Show digital downloads
     */
    public function downloads(): View
    {
        $user = Auth::user();

        $digitalOrders = $user->orders()
            ->with(['product'])
            ->whereHas('product', function($query) {
                $query->where('type', 'digital');
            })
            ->where('payment_status', 'paid')
            ->latest()
            ->paginate(15);

        return view('buyer.downloads', compact('digitalOrders'));
    }

    /**
     * Download a digital product
     */
    public function downloadProduct(Order $order)
    {
        $user = Auth::user();

        // Verify ownership and product type
        if ($order->buyer_id !== $user->id) {
            abort(403, 'Unauthorized access to this download.');
        }

        if ($order->payment_status !== 'paid') {
            abort(403, 'Product not paid for.');
        }

        if ($order->product->type !== 'digital') {
            abort(404, 'This is not a digital product.');
        }

        if (!$order->product->file_path) {
            abort(404, 'Download file not found.');
        }

        // Log download attempt
        \Log::info("Digital product download", [
            'user_id' => $user->id,
            'order_id' => $order->id,
            'product_id' => $order->product->id,
        ]);

        // Return file download
        return response()->download(
            storage_path('app/' . $order->product->file_path),
            $order->product->title . '.' . pathinfo($order->product->file_path, PATHINFO_EXTENSION)
        );
    }

    /**
     * Show wallet management page
     */
    public function wallet(): View
    {
        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();

        $transactions = $user->walletTransactions()
            ->latest()
            ->paginate(20);

        $stats = [
            'total_funded' => $user->walletTransactions()
                ->where('type', 'funding')
                ->sum('amount'),
            'total_spent' => $user->walletTransactions()
                ->where('type', 'purchase')
                ->sum('amount'),
            'total_earned' => $user->walletTransactions()
                ->where('type', 'commission')
                ->sum('amount'),
        ];

        return view('buyer.wallet', compact('wallet', 'transactions', 'stats'));
    }

    /**
     * Show learning center
     */
    public function courses(): View
    {
        $user = Auth::user();

        // Available courses
        $availableCourses = Course::active()
            ->whereNotIn('id', $user->courseEnrollments()->pluck('course_id'))
            ->paginate(12, ['*'], 'available');

        // Enrolled courses
        $enrolledCourses = $user->courseEnrollments()
            ->with(['course', 'course.lessons'])
            ->latest()
            ->paginate(12, ['*'], 'enrolled');

        return view('buyer.courses', compact('availableCourses', 'enrolledCourses'));
    }

    /**
     * Show resale earnings
     */
    public function resale(): View
    {
        $user = Auth::user();

        $stats = [
            'total_earnings' => $user->walletTransactions()
                ->where('type', 'commission')
                ->sum('amount'),
            'this_month_earnings' => $user->walletTransactions()
                ->where('type', 'commission')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'total_referrals' => $user->orders()
                ->whereNotNull('reseller_link_id')
                ->count(),
        ];

        $recentEarnings = $user->walletTransactions()
            ->where('type', 'commission')
            ->with(['relatedOrder', 'relatedOrder.product'])
            ->latest()
            ->paginate(15);

        return view('buyer.resale', compact('stats', 'recentEarnings'));
    }

    /**
     * Show followed vendors
     */
    public function following(): View
    {
        $user = Auth::user();

        $followedVendors = $user->followedVendors()
            ->with(['vendor', 'vendor.shop'])
            ->latest()
            ->paginate(15);

        return view('buyer.following', compact('followedVendors'));
    }

    /**
     * Show certificates
     */
    public function certificates(): View
    {
        $user = Auth::user();

        $certificates = $user->certificates()
            ->with(['course'])
            ->latest()
            ->paginate(12);

        return view('buyer.certificates', compact('certificates'));
    }

    /**
     * Get recommended products based on user's order history
     */
    private function getRecommendedProducts($user): \Illuminate\Database\Eloquent\Collection
    {
        // Get product types the user has ordered
        $orderedTypes = $user->orders()
            ->with('product')
            ->get()
            ->pluck('product.type')
            ->unique()
            ->filter();

        if ($orderedTypes->isEmpty()) {
            // If no order history, return popular products
            return Product::active()
                ->with(['shop'])
                ->whereHas('shop', fn($q) => $q->active())
                ->orderByDesc('orders_count')
                ->take(8)
                ->get();
        }

        // Get similar products
        return Product::active()
            ->with(['shop'])
            ->whereHas('shop', fn($q) => $q->active())
            ->whereIn('type', $orderedTypes->toArray())
            ->whereNotIn('id', $user->orders()->pluck('product_id'))
            ->orderByDesc('orders_count')
            ->take(8)
            ->get();
    }
}
