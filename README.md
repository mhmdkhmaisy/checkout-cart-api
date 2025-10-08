# RSPS Donation System

A comprehensive Laravel-based donation system for RuneScape Private Servers (RSPS) with PayPal and Coinbase Commerce integration.

## Features

- 🎮 **RSPS Integration**: Server-to-server API for secure donation processing
- 💳 **Multiple Payment Methods**: PayPal and Coinbase Commerce support
- 🔐 **Secure Authentication**: Server API key authentication for RSPS communication
- 📊 **Admin Dashboard**: Beautiful dark-themed admin panel with Cinzel font
- 🎨 **Modern UI**: Glass morphism effects with green gradient accents
- 📈 **Analytics**: Sales tracking, revenue charts, and product performance
- 🔄 **Webhook Support**: Real-time payment status updates
- 🎁 **Claim System**: In-game item claiming with proper state management

## Installation

1. **Clone and Setup**
   ```bash
   git clone <repository-url>
   cd rsps-donation-system
   composer install
   ```

2. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup**
   ```bash
   # Configure your database in .env
   php artisan migrate
   ```

4. **Configure Payment Providers**
   ```env
   # PayPal Configuration
   PAYPAL_MODE=sandbox
   PAYPAL_CLIENT_ID=your_paypal_client_id
   PAYPAL_CLIENT_SECRET=your_paypal_client_secret

   # Coinbase Commerce Configuration
   COINBASE_API_KEY=your_coinbase_api_key
   COINBASE_WEBHOOK_SECRET=your_coinbase_webhook_secret

   # RSPS Server Authentication
   RSPS_SERVER_KEY=your_secure_server_key_here
   ```

## API Documentation

### Server-Authenticated Endpoints

All server endpoints require `Authorization: Bearer <RSPS_SERVER_KEY>` header.

#### Create PayPal Checkout
```http
POST /api/checkout/paypal
Content-Type: application/json

{
  "username": "PlayerName",
  "server_id": "world1",
  "items": [
    {"product_id": 1, "qty": 3},
    {"product_id": 2, "qty": 1}
  ],
  "return_url": "https://yourdomain.com/payment/complete",
  "cancel_url": "https://yourdomain.com/payment/cancel"
}
```

#### Create Coinbase Checkout
```http
POST /api/checkout/coinbase
Content-Type: application/json

{
  "username": "PlayerName",
  "server_id": "world1",
  "items": [
    {"product_id": 1, "qty": 2}
  ],
  "metadata": {
    "rsps_user": "PlayerName"
  }
}
```

#### Claim Donations
```http
POST /api/claim
Content-Type: application/json

{
  "username": "PlayerName",
  "server_id": "world1"
}
```

#### Get Products
```http
GET /api/products
```

### Webhook Endpoints

- `POST /api/webhook/paypal` - PayPal payment notifications
- `POST /api/webhook/coinbase` - Coinbase Commerce notifications

## Database Schema

### Products Table
- `id` - Primary key
- `product_name` - Display name (e.g., "Dragon Sword Pack")
- `item_id` - RSPS item ID
- `qty_unit` - Quantity per unit purchased
- `price` - Price in USD
- `is_active` - Product availability

### Orders Table
- `id` - Primary key
- `username` - RSPS username
- `server_id` - Server identifier
- `payment_method` - 'paypal' or 'coinbase'
- `payment_id` - Provider payment ID
- `amount` - Total amount
- `status` - 'pending', 'paid', 'failed', 'cancelled'
- `claim_state` - 'not_claimed', 'claimed'

### Order Items Table
- `id` - Primary key
- `order_id` - Foreign key to orders
- `product_id` - Foreign key to products
- `qty_units` - Units purchased
- `total_qty` - Total items (qty_units × product.qty_unit)
- `claimed` - Individual item claim status

## Admin Panel

Access the admin panel at `/admin` to:

- ✅ Manage products (CRUD operations)
- 📊 View sales dashboard with analytics
- 🔍 Monitor orders with advanced filtering
- 📈 Track revenue and performance metrics

### Admin Features

- **Dark Theme**: Professional dark interface with green accents
- **Cinzel Font**: Elegant typography for premium feel
- **Glass Morphism**: Modern UI with backdrop blur effects
- **Responsive Design**: Works on desktop and mobile
- **Real-time Stats**: Live revenue and order tracking

## Security Features

- 🔐 **Server API Authentication**: Secure server-to-server communication
- ✅ **Webhook Verification**: PayPal and Coinbase signature validation
- 🛡️ **Price Validation**: Server-side price verification prevents tampering
- 🚦 **Rate Limiting**: Protection against API abuse
- 🔒 **HTTPS Required**: Secure data transmission

## RSPS Integration Example

```java
// Java example for RSPS server
public class DonationManager {
    private static final String API_URL = "https://yourdomain.com/api";
    private static final String SERVER_KEY = "your_server_key";
    
    public String createPayPalCheckout(String username, List<CartItem> items) {
        // Build request with items
        // Send POST to /api/checkout/paypal
        // Return redirect URL to player
    }
    
    public List<ClaimableItem> claimDonations(String username) {
        // Send POST to /api/claim
        // Process returned items
        // Add items to player inventory
    }
}
```

## Development

1. **Start Development Server**
   ```bash
   php artisan serve
   ```

2. **Run Migrations**
   ```bash
   php artisan migrate
   ```

3. **Seed Test Data**
   ```bash
   php artisan db:seed
   ```

## Production Deployment

1. **Environment Setup**
   - Set `APP_ENV=production`
   - Set `APP_DEBUG=false`
   - Configure proper database credentials
   - Set up SSL/HTTPS

2. **Payment Provider Setup**
   - Switch PayPal to live mode
   - Configure production API keys
   - Set up webhook endpoints

3. **Security Checklist**
   - Generate strong `RSPS_SERVER_KEY`
   - Enable rate limiting
   - Configure firewall rules
   - Set up monitoring and logging

## Support

For issues and questions:
1. Check the logs in `storage/logs/laravel.log`
2. Verify webhook signatures are working
3. Test API endpoints with proper authentication
4. Ensure database migrations are up to date

## License

This project is open-sourced software licensed under the MIT license.