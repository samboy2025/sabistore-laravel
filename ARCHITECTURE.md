# SabiStore Laravel SaaS Architecture Reference

## 🏗️ **Core Architecture Components**

### **Multi-Tenancy Strategy**
- **Shared Database** with `shop_id` scoping
- **Subdomain-based routing** (`shopname.domain.com`)
- **Tenant middleware** for automatic shop detection
- **Global shop instance** for data scoping

### **User Role Hierarchy**
```
Admin (Super User)
├── Manage all vendors & buyers
├── View all payments & transactions
├── Moderate products & shops
└── System configuration

Vendor (Shop Owner)
├── Must pay ₦1,000 membership
├── Create & manage shop
├── Upload products (physical/digital)
├── View orders & analytics
├── Generate reseller links
└── Access learning center

Buyer (Customer)
├── Browse vendor directory
├── Order via WhatsApp or platform
├── View order history
└── Download digital products
```

## 🗄️ **Database Schema**

### **Core Tables**
```sql
users (id, name, email, role, phone, whatsapp_number, membership_active)
shops (id, vendor_id, badge_id, name, slug, whatsapp_number, is_active)
products (id, shop_id, title, price, type, images, file_path, is_resellable)
orders (id, buyer_id, product_id, shop_id, status, payment_status, order_type)
payments (id, user_id, order_id, type, amount, status, gateway, reference)
badges (id, name, min_products, min_orders, min_reviews)
courses (id, title, type, content_url, category)
reseller_links (id, product_id, reseller_id, code, commission_rate)
```

### **Key Relationships**
- `User` → `Shop` (1:1 for vendors)
- `Shop` → `Products` (1:many)
- `Shop` → `Badge` (many:1)
- `Product` → `Orders` (1:many)
- `User` → `Orders` (1:many as buyer)
- `Product` → `ResellerLinks` (1:many)

## 🛡️ **Middleware Stack**

### **TenantMiddleware**
```php
// Detects subdomain and loads shop
// Sets global shop instance
// Scopes all queries to shop_id
```

### **MembershipMiddleware**
```php
// Ensures vendor has paid ₦1,000 fee
// Redirects to payment if not active
// Protects vendor-only features
```

### **RoleMiddleware**
```php
// Enforces role-based access control
// admin|vendor|buyer route protection
```

## 🌐 **Routing Structure**

### **Main Domain Routes**
```php
/ (homepage)
/vendors (directory)
/login, /register (auth)
/admin/* (admin panel)
/vendor/* (vendor dashboard)
/buyer/* (buyer dashboard)
/membership/payment (vendor payment)
```

### **Subdomain Routes**
```php
{shop}.domain.com/
{shop}.domain.com/products
{shop}.domain.com/products/{id}
{shop}.domain.com/r/{code} (reseller tracking)
```

## 💰 **Payment Flow**

### **Membership Payment (Vendors)**
1. Vendor registers
2. Redirected to `/membership/payment`
3. Paystack integration (₦1,000)
4. Webhook confirms payment
5. `membership_active = true`
6. Access to vendor dashboard

### **Product Orders**
1. **WhatsApp**: Direct link to vendor WhatsApp
2. **Platform**: Full checkout with address/payment

## 🏅 **Badge System Logic**

### **Auto-Update Triggers**
- New product upload
- Order completion
- Review submission (if implemented)

### **Badge Levels**
```php
Bronze: Default (payment complete)
Silver: 5+ products, 5+ orders
Gold: 10+ products, 10+ orders, 1+ review
Top Vendor: 20+ products, 25+ orders, 5+ reviews
```

## 🔗 **WhatsApp Integration**

### **Product Order Links**
```php
$whatsappLink = "https://wa.me/{$shop->whatsapp_number}?text=" 
    . urlencode("I'm interested in {$product->title} - ₦{$product->price}");
```

### **No API Integration**
- Click-to-chat links only
- No automated messaging
- Manual vendor communication

## 📊 **Data Scoping Strategy**

### **Vendor Data Isolation**
```php
// All vendor queries automatically scoped
Product::where('shop_id', $currentShop->id)->get();
Order::whereHas('product', fn($q) => $q->where('shop_id', $shopId))->get();
```

### **Global Shop Access**
```php
// In TenantMiddleware
app()->instance('current_shop', $shop);

// In controllers
$shop = app('current_shop');
```

## 🎓 **Learning Center**

### **Content Types**
- Video courses (YouTube embeds)
- PDF documents
- Article content

### **Vendor Progress Tracking**
- Course completion status
- Progress indicators
- Business skill development

## 💼 **Reseller System**

### **Commission Tracking**
```php
// Generate unique reseller codes
$code = strtoupper(Str::random(8));

// Track clicks and conversions
$resellerLink->recordClick();
$resellerLink->recordSale($commissionAmount);
```

### **Revenue Sharing**
- Configurable commission rates
- Automatic calculation
- Reseller earnings dashboard

## 🛠️ **Key Design Patterns**

### **Repository Pattern** (Optional)
```php
interface ShopRepositoryInterface
{
    public function getActiveShops();
    public function findBySlug($slug);
}
```

### **Service Layer**
```php
class BadgeService
{
    public function updateShopBadge(Shop $shop);
    public function checkBadgeEligibility(Shop $shop);
}
```

### **Event-Driven Updates**
```php
// When order is completed
event(new OrderCompleted($order));

// Update badge automatically
class UpdateShopBadge implements ShouldQueue
{
    public function handle(OrderCompleted $event) {
        // Badge logic
    }
}
```

## 📱 **Frontend Architecture**

### **Blade + Tailwind Structure**
```
resources/views/
├── layouts/
│   ├── app.blade.php (main layout)
│   ├── admin.blade.php (admin layout)
│   ├── vendor.blade.php (vendor layout)
│   └── shop.blade.php (public shop layout)
├── admin/ (admin views)
├── vendor/ (vendor dashboard views)
├── buyer/ (buyer dashboard views)
├── public/ (homepage, directory)
└── shop/ (subdomain shop views)
```

### **Component Organization**
```php
// Blade components
@component('components.product-card', ['product' => $product])
@component('components.badge', ['badge' => $shop->badge])
@component('components.whatsapp-button', ['link' => $product->whatsapp_order_link])
```

## 🚀 **Performance Considerations**

### **Caching Strategy**
- Shop data caching by subdomain
- Product listings cache
- Badge calculations cache

### **Database Optimization**
- Indexed shop_id columns
- Optimized tenant queries
- Eager loading relationships

### **CDN Integration**
- Product images
- Course videos
- Static assets

---

**This architecture provides a scalable, maintainable foundation for the SabiStore multi-tenant SaaS platform with clear separation of concerns and proper data isolation.** 