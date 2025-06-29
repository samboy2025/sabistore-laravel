You are helping me build a Laravel SaaS multi-tenant web app for vendors and buyers. Now implement the following complete functionality update and extend existing models and features.

📌 GOAL:
Build a full **resale and wallet payment system** connected to vendor public pages and product logic.

---

✅ PUBLIC VENDOR PAGE (VIA SUBDOMAIN)
- Display vendor image/logo, name, short description, badge
- Show a grid of all public products uploaded by the vendor
- Each product card should have:
  - Title, price, image
  - “Buy Now” button (to trigger wallet payment flow)
  - “Order via WhatsApp” button (pre-filled message with product name)
- The product detail page should show:
  - Product information
  - Whether it’s a **resellable** product and the **reseller commission %**
  - Resell link if user is logged in
  - Option to buy directly from wallet (skip WhatsApp)

---

✅ PRODUCT CREATION (BY VENDOR)
- Add a new field to products: `resell_commission_percent`
  - This allows the vendor to set a % (e.g. 10%) for resellers
  - Must be between 0% and 50%
- Checkbox for “Allow this product to be resold”
- Save both values in the database

---

✅ WALLET SYSTEM (FOR ALL USERS)
- Every user (vendor or buyer) should have a wallet
- Users can fund wallet using Paystack integration
- Admin can also top-up or adjust wallet manually

🔐 WALLET MODEL:
- `user_id`
- `balance`
- `last_updated_at`

🔐 WALLET TRANSACTIONS TABLE:
- `id`
- `user_id`
- `type` (funding, purchase, commission, withdrawal, admin_adjustment)
- `amount`
- `balance_after`
- `reference` (Paystack or internal ID)
- `description`
- `status` (pending, completed, failed)

---

✅ FUNDING WALLET
- User goes to wallet page, enters amount (₦500 minimum)
- Generates a Paystack payment link
- After success, use webhook to update wallet balance and record transaction

---

✅ PRODUCT PURCHASE WITH WALLET
- Buyer can buy any product directly from vendor page using their wallet
- Buyer must have sufficient balance
- After purchase:
  - Deduct amount from buyer’s wallet
  - Credit vendor’s wallet with amount minus resale commission (if applicable)
  - If a reseller referred the buyer, credit reseller's wallet with their commission
  - Record all wallet transactions

---

✅ RESELLER LOGIC
- If a buyer clicks a reseller link and buys the product:
  - Save `reseller_id` in the purchase
  - Calculate reseller's commission based on `resell_commission_percent`
  - Pay reseller into wallet after successful order
- Show reseller performance in their dashboard (clicks, sales, earnings)

---

✅ ADMIN PANEL
- Admin can:
  - View all wallet balances
  - View wallet transaction history per user
  - Add funds manually to a user
  - Reverse or correct failed wallet transactions
  - View all purchases, resell payouts, and vendor earnings

---

✅ IMPORTANT LOGIC
- Never allow a wallet to go negative
- All wallet changes must be logged in `wallet_transactions` table
- Funded balance and earned commissions are all the same wallet
- Vendor must be paid via wallet (no direct bank transfer at this stage)
- Add `wallet_balance` to user dashboard

---

🧾 REQUIRED API ENDPOINTS
- `POST /api/wallet/fund` (initiate Paystack payment)
- `POST /api/wallet/fund/callback` (Paystack webhook)
- `GET /api/wallet` (show balance and transactions)
- `POST /api/products/{id}/buy` (buy product with wallet)
- `GET /api/reseller-link/{code}` (track clicks, set session reseller_id)
- `POST /api/admin/wallet/adjust` (admin wallet adjustments)

💰 Example Flow:
1. Buyer clicks on vendor product
2. Chooses to “Buy Now”
3. If wallet balance is enough, payment is deducted
4. Vendor gets paid in wallet
5. If purchased via reseller link, reseller gets % into their wallet
6. Admin can track and export wallet reports

---

📦 GOAL:
Build this system in a modular, maintainable, and secure way. Don’t skip any wallet transaction logs. Always reflect accurate balances. Ask me questions before making assumptions.
