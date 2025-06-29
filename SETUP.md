# SabiStore Laravel Multi-Tenant SaaS Setup Guide

## 🎯 Project Overview
This is a Laravel-based multi-tenant SaaS application for vendors and digital product sellers in Nigeria. The app supports:

- **Multi-tenancy via subdomains** (e.g., `shopname.sabistore.com`)
- **Three user roles**: Admin, Vendor, Buyer
- **Membership payment system** (₦1,000 for vendors)
- **Product management** (physical & digital products)
- **WhatsApp ordering** (click-to-chat links)
- **Badge system** (Bronze, Silver, Gold, Top Vendor)
- **Learning center** for vendor training
- **Reseller system** with commission tracking

## 🚀 **STEP 1: Database Setup**

### 1.1 Create Database
Create a MySQL database called `sabistore_db`:

```sql
CREATE DATABASE sabistore_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 1.2 Update Database Credentials
Update your `.env` file with correct database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sabistore_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## 🚀 **STEP 2: Run Migrations and Seeders**

```bash
# Run migrations
php artisan migrate

# Seed the database with initial data
php artisan db:seed
```

This will create:
- All database tables
- Default badge levels (Bronze, Silver, Gold, Top Vendor)
- Admin user (`admin@sabistore.com` / `password123`)

## 🚀 **STEP 3: Payment Gateway Configuration**

### 3.1 Paystack Setup
1. Sign up at [Paystack](https://paystack.com)
2. Get your API keys from the dashboard
3. Update `.env`:

```env
PAYSTACK_PUBLIC_KEY=pk_test_your_actual_paystack_public_key
PAYSTACK_SECRET_KEY=sk_test_your_actual_paystack_secret_key
PAYSTACK_CALLBACK_URL="${APP_URL}/membership/callback"
MEMBERSHIP_FEE=100000  # ₦1,000 in kobo
```

### 3.2 Webhook Setup
Set up Paystack webhook URL: `https://yourdomain.com/membership/callback`

## 🚀 **STEP 4: Domain Configuration**

### 4.1 Local Development
For local development, update your `.env`:

```env
APP_DOMAIN=localhost
```

Add to your `hosts` file (`C:\Windows\System32\drivers\etc\hosts` on Windows):
```
127.0.0.1 localhost
127.0.0.1 shop1.localhost
127.0.0.1 shop2.localhost
```

### 4.2 Production Setup
For production:

```env
APP_DOMAIN=sabistore.com
```

Configure wildcard DNS: `*.sabistore.com` → Your server IP

## 🚀 **STEP 5: Storage Configuration**

```bash
# Create storage symlink
php artisan storage:link

# Set permissions (Linux/Mac)
chmod -R 775 storage bootstrap/cache
```

## 🚀 **STEP 6: Create Sample Controllers**

### 6.1 Public Home Controller
```bash
php artisan make:controller Public/HomeController
```

### 6.2 Admin Dashboard Controller
```bash
php artisan make:controller Admin/AdminDashboardController
```

### 6.3 Vendor Dashboard Controller
```bash
php artisan make:controller Vendor/VendorDashboardController
```

### 6.4 Payment Controller
```bash
php artisan make:controller Payment/MembershipPaymentController
```

## 🚀 **STEP 7: Frontend Setup**

### 7.1 Install Frontend Dependencies
```bash
npm install
```

### 7.2 Initialize Tailwind CSS
```bash
npx tailwindcss init
```

### 7.3 Configure Tailwind
Update `tailwind.config.js`:

```js
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        'brand-red': '#B10020',
        'brand-gray': '#F3F3F3',
      }
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
```

### 7.4 Update CSS
In `resources/css/app.css`:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

/* Custom brand styles */
.btn-primary {
  @apply bg-brand-red text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors;
}

.btn-secondary {
  @apply bg-white text-brand-red border border-brand-red px-4 py-2 rounded-lg hover:bg-red-50 transition-colors;
}
```

## 🚀 **STEP 8: Key Features Implementation**

### 8.1 Middleware Registration ✅
The following middleware have been created and registered:
- `TenantMiddleware` - Subdomain detection
- `MembershipMiddleware` - Vendor payment verification  
- `RoleMiddleware` - Role-based access control

### 8.2 Models Created ✅
All core models with relationships:
- `User` (with roles: admin, vendor, buyer)
- `Shop` (vendor shops with subdomain routing)
- `Product` (physical & digital with WhatsApp links)
- `Order` (platform & WhatsApp orders)
- `Payment` (membership & product payments)
- `Badge` (vendor achievement system)
- `Course` (learning center content)
- `ResellerLink` (affiliate tracking)

### 8.3 Route Structure ✅
Complete route structure for:
- Public pages (homepage, vendor directory)
- Authentication (login/register)
- Admin panel (users, shops, payments)
- Vendor dashboard (products, orders, learning)
- Buyer dashboard (orders, downloads)
- Subdomain shops (tenant routing)

## 🚀 **STEP 9: Testing the Setup**

### 9.1 Access Points
- **Main site**: `http://localhost`
- **Admin panel**: `http://localhost/admin/dashboard`
- **Vendor dashboard**: `http://localhost/vendor/dashboard`
- **Shop subdomain**: `http://shop1.localhost` (after creating shop)

### 9.2 Default Login Credentials
- **Admin**: `admin@sabistore.com` / `password123`

### 9.3 Test Workflow
1. Register as vendor
2. Pay membership fee (₦1,000)
3. Complete shop setup
4. Upload products
5. Test subdomain access
6. Generate reseller links
7. Test badge system

## 🚀 **STEP 10: Production Deployment**

### 10.1 Server Requirements
- PHP 8.2+
- MySQL 8.0+
- Nginx/Apache with wildcard subdomain support
- SSL certificate for `*.yourdomain.com`

### 10.2 Environment Configuration
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sabistore.com
APP_DOMAIN=sabistore.com
```

### 10.3 Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## 🚀 **STEP 11: Key Business Logic**

### 11.1 Vendor Onboarding Flow
1. Register → Choose "vendor" role
2. Redirected to membership payment
3. Pay ₦1,000 via Paystack
4. Complete shop setup (name, WhatsApp, BVN/NIN)
5. Upload products (after payment verification)
6. Get public subdomain: `shopname.sabistore.com`

### 11.2 Badge System Logic
- **Bronze**: Default after payment
- **Silver**: 5+ products & 5+ orders
- **Gold**: 10+ products & 10+ orders & 1+ review
- **Top Vendor**: 20+ products & 25+ orders & 5+ reviews

### 11.3 WhatsApp Integration
Products generate WhatsApp links:
```php
https://wa.me/{vendor_phone}?text=I'm%20interested%20in%20{product_name}
```

## 🚀 **STEP 12: Security Considerations**

### 12.1 Data Scoping
All vendor data is scoped by `shop_id` to ensure multi-tenant isolation.

### 12.2 Payment Verification
Vendors cannot upload products until membership payment is confirmed via webhook.

### 12.3 Subdomain Validation
Only active shops with completed setup get live subdomains.

## 🛠 **Next Steps After Setup**

1. **Create blade templates** for all views
2. **Implement payment webhook** handlers
3. **Add file upload** functionality for products
4. **Build admin dashboard** interface
5. **Create vendor dashboard** UI
6. **Implement WhatsApp click tracking**
7. **Add email notifications**
8. **Setup automated badge updates**

## 📞 **Support & Documentation**

For issues or questions:
1. Check Laravel documentation
2. Review Paystack integration docs
3. Test subdomain routing locally
4. Verify database connections

---

**This setup provides a complete foundation for the SabiStore multi-tenant SaaS platform. All core architecture, models, migrations, and routing are in place.** 