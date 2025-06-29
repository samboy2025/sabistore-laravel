🎯 PRODUCT OVERVIEW
This is a web-based multi-tenant SaaS platform built with Laravel. It allows vendors (individuals, shop owners, service providers, and digital product sellers) to create an online store with a custom subdomain. Vendors can sell both physical and digital products, collect orders via WhatsApp or directly on the platform, and allow resellers to promote their products with commission tracking. Vendors must pay a membership fee before unlocking their shop features.

Buyers can browse shops, place orders, and view their order history. Vendors also have access to a learning portal for business skills and a badge system to showcase their progress and trust level publicly.

🧭 USER TYPES
Admin

Vendor (Shop Owner)

Buyer (Customer / Reseller)

📚 MAIN FEATURES LIST (GROUPED BY USER TYPE)
👨‍💼 Admin Panel Pages
Dashboard Overview (users, sales, active shops, etc.)

Vendor List & Management

Buyer List

Payment Transactions

Membership Payments Management

Product Moderation Panel

Reseller Activity Tracker

Badge Settings (create/edit rules)

Course Manager (add/update vendor courses)

Site Settings (logo, colors, homepage content)

🛍 Vendor Dashboard Pages (after login & membership payment)
Dashboard Home

Shop stats, badge status, welcome message

Shop Setup

Shop name

WhatsApp number

Description

Upload business video

Upload logo, banner

BVN/NIN verification

Select categories

Membership Payment Page

Vendor must pay ₦1,000 before uploading products

Product Manager

Create new product (physical or digital)

Add title, price, description, tags

Upload images / upload file (if digital)

Choose if product is resellable

View product list (edit, delete)

Order Management

View customer orders (paid/unpaid)

Order details (shipping info, product, status)

Reseller Links

Generate/share reseller links

See which reseller sold what

Learning Center

Access free vendor training courses (videos, PDFs)

Badge Progress Page

See your badge level

Checklist of how to reach next badge (orders, reviews, uploads)

Shop Preview

View public shop link (subdomain)

🛒 Buyer Dashboard Pages
Dashboard Home (orders overview)

Profile Settings (name, email, phone, password)

My Orders (all purchased items)

Download Center (digital purchases)

Wishlist / Saved Products

🌐 Public Pages
Homepage

Hero section

Platform benefits

Call to action for vendors to join

Call to action for buyers to browse shops

Register Page (Choose: Vendor or Buyer)

Vendor: full name, email, password, phone, WhatsApp, shop name, BVN/NIN, video upload

Buyer: full name, phone number, email, password

Login Page

For both buyer and vendor (redirect to correct dashboard)

Vendor Directory Page

Grid layout of all vendors with:

Logo

Shop name

Badge (Bronze, Silver, etc.)

Short tagline

“View Shop” button

Public Shop Page (on subdomain)

Vendor banner, logo, about info, video

Badge shown

Product grid layout

Product detail view (with:

Name, price, image

“Order on WhatsApp” button

“Buy Now” on platform button

Reseller link option if enabled)

Product Detail Page (if accessed directly)

Description

Price

Images/files

Buy/Order buttons

Seller info

🔐 ACCESS CONTROL & BEHAVIOUR
Vendors cannot upload any product or activate public shop until ₦1,000 membership fee is paid.

Only verified vendors (with payment and profile setup) get a live subdomain.

Only registered buyers can order on the platform.

Products marked as resellable generate a reseller link that can be tracked by the vendor.

💸 PAYMENTS
Vendors pay membership (₦1,000) via Paystack or Flutterwave.

Buyers pay for products (if ordering via the platform) via integrated payment.

Admin can view all payments and transaction records.

🏅 BADGE SYSTEM
Badge levels:

Bronze (default after payment)

Silver (5+ product uploads and 5 sales)

Gold (10+ sales and 1 positive review)

Top Vendor (25+ orders and multiple reviews)

Badges are displayed on:

Vendor dashboard

Public vendor directory

Public shop page

Product pages

🧑‍🏫 LEARNING CENTER FOR VENDORS
Free training materials in the dashboard:

Videos on sales, marketing, WhatsApp use, branding, etc.

PDFs or embedded YouTube videos

Can be marked as “Completed”

Completion doesn’t unlock features, it’s for empowerment only

🌐 SUBDOMAIN SYSTEM
Each vendor’s shop has a subdomain:

Format: shopname.platformdomain.com

Publicly accessible

Automatically routed using Laravel multi-tenancy

📱 WHATSAPP ORDERING
Each product has a WhatsApp Order button

Auto-fills a message with product name and price

Redirects user to WhatsApp with vendor’s number

Vendor chat feature (DM between buyer and vendor)

Delivery tracking system

Subscription packages for vendors (more storage, analytics)

Advanced search & filtering

Review and rating system for products/vendors
vendo can manage their domains — change subdomains and add custom 2nd level domains.

