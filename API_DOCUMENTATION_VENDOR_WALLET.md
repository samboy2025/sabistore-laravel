# 💰 Vendor Wallet API Documentation

## Overview
The Vendor Wallet API provides comprehensive wallet management functionality for vendors, including funding, transaction history, and automatic commission processing.

## Base URL
```
https://your-domain.com/vendor/wallet
```

## Authentication
All endpoints require:
- User authentication (`auth` middleware)
- Vendor role (`role:vendor` middleware)
- Active membership (`membership` middleware)

---

## 📋 **Endpoints**

### 1. **GET /vendor/wallet**
Display the vendor wallet dashboard

**Response:** HTML view with wallet information
- Wallet balance and statistics
- Transaction history (paginated)
- Monthly activity charts
- Funding interface

**Example:**
```http
GET /vendor/wallet
Authorization: Bearer {token}
```

---

### 2. **POST /vendor/wallet/fund**
Initiate wallet funding via Paystack

**Request Body:**
```json
{
    "amount": 5000
}
```

**Validation:**
- `amount`: required, numeric, min:500, max:1000000

**Response:**
```json
{
    "success": true,
    "data": {
        "authorization_url": "https://checkout.paystack.com/...",
        "reference": "wallet_abc123_1640995200"
    }
}
```

**Error Response:**
```json
{
    "success": false,
    "message": "Payment initialization failed"
}
```

---

### 3. **GET /vendor/wallet/callback**
Handle Paystack payment callback

**Query Parameters:**
- `reference`: Payment reference from Paystack

**Success Response:** Redirect to wallet dashboard with success message
**Error Response:** Redirect to wallet dashboard with error message

**Example:**
```http
GET /vendor/wallet/callback?reference=wallet_abc123_1640995200
```

---

## 🔄 **Commission Processing API**

### Automatic Processing
The system automatically processes reseller commissions when:
1. Order status changes to "delivered"
2. Vendor funds their wallet (processes pending commissions)

### Commission Service Methods

#### `processResellerCommission(Order $order)`
```php
$commissionService = app(\App\Services\CommissionService::class);
$result = $commissionService->processResellerCommission($order);

// Response structure:
[
    'success' => true|false,
    'message' => 'Commission processed successfully',
    'commission_amount' => 150.00,
    'vendor_balance' => 2500.00,
    'reseller_balance' => 650.00,
    'pending' => false // true if insufficient balance
]
```

#### `processPendingCommissions(User $vendor)`
```php
$result = $commissionService->processPendingCommissions($vendor);

// Response structure:
[
    'processed' => 3,
    'failed' => 0,
    'total_amount' => 450.00
]
```

#### `getPendingCommissionSummary(User $vendor)`
```php
$summary = $commissionService->getPendingCommissionSummary($vendor);

// Response structure:
[
    'count' => 2,
    'total_amount' => 300.00,
    'transactions' => Collection // WalletTransaction objects
]
```

---

## 📊 **Data Structures**

### Wallet Transaction
```json
{
    "id": 123,
    "user_id": 456,
    "type": "funding|commission|purchase|withdrawal|admin_adjustment",
    "amount": 1000.00,
    "balance_after": 2500.00,
    "reference": "wallet_abc123_1640995200",
    "description": "Wallet funding of ₦1,000.00",
    "status": "pending|completed|failed",
    "related_order_id": 789,
    "related_user_id": 101,
    "metadata": {},
    "created_at": "2024-01-01T12:00:00Z",
    "updated_at": "2024-01-01T12:00:00Z"
}
```

### Commission Transaction
```json
{
    "vendor_transaction": {
        "type": "commission",
        "amount": -150.00,
        "description": "Commission payment to John Doe for order #ORD-123"
    },
    "reseller_transaction": {
        "type": "commission", 
        "amount": 150.00,
        "description": "Commission earned from Shop ABC for order #ORD-123"
    }
}
```

---

## 🔐 **Security Features**

### Payment Security
- Paystack integration with secure tokenization
- Transaction verification via Paystack API
- Reference validation and duplicate prevention

### Wallet Security
- Balance validation before debits
- Transaction atomicity with database transactions
- Comprehensive audit logging

### Commission Security
- Automatic calculation based on product settings
- Vendor balance verification
- Pending transaction handling for insufficient funds

---

## 📈 **Monitoring & Analytics**

### Key Metrics
```php
// Wallet Statistics
$stats = [
    'total_funded' => 15000.00,
    'total_spent' => 8500.00,
    'total_earned' => 2300.00,
    'commission_paid' => 1200.00,
    'pending_transactions' => 3
];

// Monthly Data for Charts
$monthlyData = [
    'months' => ['Nov 2024', 'Dec 2024', 'Jan 2025'],
    'funding' => [5000, 3000, 7000],
    'spending' => [2000, 1500, 3500]
];
```

### Error Tracking
```php
// Commission Processing Errors
Log::error('Commission processing failed', [
    'order_id' => $order->id,
    'vendor_id' => $vendor->id,
    'reseller_id' => $reseller->id,
    'commission_amount' => $amount,
    'error' => $exception->getMessage()
]);

// Payment Errors
Log::error('Wallet funding failed', [
    'user_id' => $user->id,
    'amount' => $amount,
    'reference' => $reference,
    'paystack_response' => $response
]);
```

---

## 🧪 **Testing Examples**

### Test Wallet Funding
```bash
curl -X POST https://your-domain.com/vendor/wallet/fund \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{"amount": 5000}'
```

### Test Commission Processing
```php
// Create test order with reseller
$order = Order::factory()->create([
    'reseller_id' => $reseller->id,
    'reseller_commission' => 150.00,
    'status' => 'processing'
]);

// Mark as delivered (triggers commission processing)
$order->markAsDelivered();

// Verify commission was processed
$this->assertTrue($order->fresh()->commission_paid);
```

### Test Pending Commission Processing
```php
// Create vendor with insufficient balance
$vendor = User::factory()->vendor()->create();
$vendor->getOrCreateWallet()->update(['balance' => 50.00]);

// Create order requiring 150.00 commission
$order = Order::factory()->create([
    'reseller_commission' => 150.00
]);

// Should create pending transaction
$order->markAsDelivered();
$this->assertFalse($order->fresh()->commission_paid);

// Fund wallet and verify processing
$vendor->getOrCreateWallet()->credit(200.00, 'funding', 'Test funding');
$commissionService->processPendingCommissions($vendor);
$this->assertTrue($order->fresh()->commission_paid);
```

---

## 🚨 **Error Handling**

### Common Error Codes
- `400`: Invalid request data
- `401`: Unauthorized access
- `403`: Insufficient permissions
- `422`: Validation errors
- `500`: Server/payment processing errors

### Error Response Format
```json
{
    "success": false,
    "message": "Detailed error message",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```

This API documentation provides comprehensive coverage of the vendor wallet system with examples and testing guidance.
