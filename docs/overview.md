Your App is a Multi-Tenant Laravel SaaS
🎯 Core Idea:
Each vendor/shop owner is treated like a tenant — with:

A subdomain (vendorname.yourdomain.com)

Separate shop and products

A shared database (at MVP level) but logically separated per user/shop

🔧 TECHNOLOGY STACK
Layer	Tools
Framework	Laravel 11 (or latest)
Frontend	Blade + Tailwind CSS
Subdomains	Wildcard Subdomains in Laravel
Payments	Paystack / Flutterwave
Auth	Laravel Breeze or Laravel Fortify
DB	Shared DB with shop_id separation (use Spatie/Landlord package if needed)
Media Uploads	Cloudinary / S3 / local storage
Notifications	WhatsApp API + Email

🧱 PROJECT STRUCTURE PLAN
Here’s a basic folder & model layout for your MVP:

📁 Models:
User (Vendor or Buyer)

Shop (represents the Vendor’s Store)

Product (belongs to Shop)

Order (tracks orders from buyers)

ResellerLink (for referral tracking)

Badge (Bronze, Silver, etc.)

Course (for vendor learning)

Payment (membership + product purchases)

🚦 ROUTE STRUCTURE
🌐 Public Routes:
Route	Purpose
/	Landing Page
/vendors	Vendor Directory
/register / /login	Auth Routes
/course-library	Free course page
https://vendorname.yourdomain.com	Vendor Shop Subdomain
/product/{id}	Product Detail Page

🔐 Vendor Dashboard (after login):
Path	Purpose
/dashboard	Vendor Home
/products	Manage Products
/resellers	Track Resellers
/badge-status	View Badge & Stats
/learning-center	Access Courses

🧠 MULTI-TENANCY STRATEGY (Recommended)
✅ For MVP — use a shared database multi-tenancy approach.
This is easier to maintain and scale for now.

🛠️ Recommended Laravel Tools:
Tenancy for Laravel (great for full subdomain isolation)

Stancl/tenancy (easy to use for Laravel 9+)

Spatie Multitenancy if you want role separation

🔑 What You Get:
Automatic subdomain routing like samboy.yourdomain.com

Each vendor's data scoped to their shop_id

Middleware for tenant detection and scoping

💰 MEMBERSHIP PAYMENT FLOW
Vendor Registers → Redirect to /pay-membership

Payment (Paystack / Flutterwave)

On Success: Allow product uploads

Save payment in payments table

🏆 BADGE SYSTEM
Create a badges table:

php
Copy
Edit
Schema::create('badges', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // Bronze, Silver, etc.
    $table->integer('min_orders')->default(0);
    $table->integer('min_products')->default(0);
    $table->timestamps();
});
Assign to shops table based on performance metrics:

php
Copy
Edit
$shop->badge_id = $badge->id;
Update badges periodically via a scheduled Artisan command.

