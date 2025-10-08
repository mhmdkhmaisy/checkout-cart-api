# Webhook Security & Logging Fixes

This document outlines the comprehensive fixes applied to the checkout cart API to address security vulnerabilities and improve webhook handling.

## 🔧 Issues Fixed

### 1. **PayPal Webhook Verification** ✅
- **Before**: No signature verification - anyone could spoof webhooks
- **After**: Full signature verification using PayPal's `/v1/notifications/verify-webhook-signature` endpoint
- **Implementation**: `WebhookService::verifyPayPalWebhook()`

### 2. **Improved Event Handling** ✅
- **Before**: Processed both `CHECKOUT.ORDER.APPROVED` and `PAYMENT.CAPTURE.COMPLETED`
- **After**: Only finalizes orders on `PAYMENT.CAPTURE.COMPLETED` (when money is actually captured)
- **Bonus**: Logs `CHECKOUT.ORDER.APPROVED` for monitoring without status updates

### 3. **Standardized Response Format** ✅
- **Before**: Inconsistent response formats
- **After**: All responses follow standard format:
  - Success: `{'success': true, 'message': 'Description'}`
  - Error: `{'success': false, 'error': 'Reason'}`

### 4. **Enhanced Coinbase Event Handling** ✅
- **Before**: Only handled `charge:confirmed`
- **After**: Handles all important Coinbase events:
  - `charge:confirmed` → `paid`
  - `charge:failed` → `failed`
  - `charge:pending` → `pending`
  - `charge:canceled` → `failed`
  - `charge:resolved` → `refunded`

### 5. **Robust Order Lookup & Safety** ✅
- **Before**: Trusted webhook payload without proper validation
- **After**: 
  - Always validates order existence
  - Logs warnings for missing orders
  - Returns success for non-existent orders to prevent retries
  - Implements proper idempotency checks

### 6. **Dual-Table Logging System** ✅
- **New**: `order_logs` table for current state tracking
- **New**: `order_events` table for complete audit history
- **Benefits**: Fast queries + complete audit trail

## 📊 Database Schema

### New Tables

#### `order_logs` - Current State
```sql
CREATE TABLE order_logs (
    id BIGINT PRIMARY KEY,
    order_id BIGINT FOREIGN KEY,
    user_id VARCHAR(255),
    status ENUM('pending','paid','failed','refunded','claimed','reserved'),
    last_event VARCHAR(255),
    payload JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### `order_events` - Full History
```sql
CREATE TABLE order_events (
    id BIGINT PRIMARY KEY,
    order_id BIGINT FOREIGN KEY,
    event_type VARCHAR(255),
    status ENUM('pending','paid','failed','refunded','claimed','reserved'),
    payload JSON,
    created_at TIMESTAMP
);
```

## 🔐 Security Improvements

### PayPal Webhook Verification
```php
// Verifies these headers:
- PAYPAL-AUTH-ALGO
- PAYPAL-CERT-URL
- PAYPAL-TRANSMISSION-ID
- PAYPAL-TRANSMISSION-SIG
- PAYPAL-TRANSMISSION-TIME
```

### Coinbase Webhook Verification
```php
// HMAC SHA256 verification
hash_hmac('sha256', $payload, $webhook_secret)
```

## 📋 Event Status Mapping

| Provider | Event Type | Order Status | Action |
|----------|------------|--------------|---------|
| PayPal | `CHECKOUT.ORDER.APPROVED` | `pending` | Log only |
| PayPal | `PAYMENT.CAPTURE.COMPLETED` | `paid` | Update & log |
| PayPal | `PAYMENT.CAPTURE.DENIED` | `failed` | Update & log |
| PayPal | `PAYMENT.CAPTURE.REFUNDED` | `refunded` | Update & log |
| PayPal | `PAYMENT.CAPTURE.REVERSED` | `refunded` | Update & log |
| Coinbase | `charge:pending` | `pending` | Update & log |
| Coinbase | `charge:confirmed` | `paid` | Update & log |
| Coinbase | `charge:failed` | `failed` | Update & log |
| Coinbase | `charge:canceled` | `failed` | Update & log |
| Coinbase | `charge:resolved` | `refunded` | Update & log |

## 🛠️ Configuration Required

Add these to your `.env` file:

```env
# PayPal
PAYPAL_BASE_URL=https://api-m.sandbox.paypal.com
PAYPAL_CLIENT_ID=your_client_id
PAYPAL_CLIENT_SECRET=your_client_secret
PAYPAL_WEBHOOK_ID=your_webhook_id

# Coinbase
COINBASE_API_KEY=your_api_key
COINBASE_WEBHOOK_SECRET=your_webhook_secret
```

## 🚀 New Admin Features

### API Endpoints

1. **GET** `/api/admin/orders/logs` - View order logs with filters
2. **GET** `/api/admin/orders/{id}/events` - View full event history
3. **GET** `/api/admin/orders/stats` - Order statistics
4. **PATCH** `/api/admin/orders/{id}/status` - Manual status updates

### Available Filters
- Status (`pending`, `paid`, `failed`, `refunded`, `claimed`, `reserved`)
- User ID
- Order ID  
- Event type
- Date range

## 🔄 Migration Commands

Run these commands to apply the database changes:

```bash
php artisan migrate
```

## 📝 Logging

All webhook events are now comprehensively logged:

- **Incoming webhooks**: Full payload + headers
- **Signature verification**: Success/failure with details
- **Order updates**: Old status → new status
- **Missing orders**: Warnings for potential fraud
- **Unknown events**: Info logs for new event types

## 🎯 Claim Logic

Orders can only be claimed when:
- `orders.status = 'paid'`
- `order_items.claimed = 0`

Use the `Order::canBeClaimed()` method for validation.

## 🔍 Testing Webhooks

### PayPal Test
```bash
curl -X POST http://your-domain/api/webhooks/paypal \
  -H "Content-Type: application/json" \
  -H "PAYPAL-TRANSMISSION-ID: test-id" \
  -H "PAYPAL-AUTH-ALGO: SHA256withRSA" \
  -H "PAYPAL-CERT-URL: https://api.sandbox.paypal.com/cert" \
  -H "PAYPAL-TRANSMISSION-SIG: test-signature" \
  -H "PAYPAL-TRANSMISSION-TIME: 2024-01-01T00:00:00Z" \
  -d '{"event_type":"PAYMENT.CAPTURE.COMPLETED","resource":{"id":"test-payment-id"}}'
```

### Coinbase Test
```bash
curl -X POST http://your-domain/api/webhooks/coinbase \
  -H "Content-Type: application/json" \
  -H "X-CC-Webhook-Signature: test-signature" \
  -d '{"event":{"type":"charge:confirmed","data":{"id":"test-charge-id"}}}'
```

## ⚡ Performance Notes

- `order_logs` table has unique constraint on `order_id` for fast lookups
- Indexes on `status`, `updated_at`, `event_type`, and `created_at`
- Webhook responses are optimized to prevent unnecessary retries

## 🛡️ Security Best Practices

1. **Always verify signatures** before processing webhooks
2. **Log everything** for audit trails and debugging
3. **Use idempotency** to prevent duplicate processing
4. **Return success** for non-existent orders to prevent retry storms
5. **Validate all inputs** from webhook payloads
6. **Use HTTPS** for all webhook endpoints
7. **Rotate webhook secrets** regularly

---

All fixes have been implemented and are ready for testing. The system now provides robust webhook handling with comprehensive logging and security measures.