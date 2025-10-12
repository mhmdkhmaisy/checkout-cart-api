# RSPS Complete System

A comprehensive Laravel-based system for RuneScape Private Servers (RSPS) featuring donation management, cache distribution, voting system, and client management with PayPal and Coinbase Commerce integration.

## 🚀 Features

### 💰 Donation System
- 🎮 **RSPS Integration**: Server-to-server API for secure donation processing
- 💳 **Multiple Payment Methods**: PayPal and Coinbase Commerce support
- 🔐 **Secure Authentication**: Server API key authentication for RSPS communication
- 🎁 **Claim System**: In-game item claiming with proper state management

### 📦 Cache Management System
- 🗂️ **Directory Structure Preservation**: Maintains folder hierarchy during upload/download
- 📁 **Multi-Format Support**: Handles all file types (DAT, IDX, models, textures, etc.)
- 🔄 **Intelligent Bundling**: Creates compressed archives with structure intact
- 📊 **Advanced Analytics**: File statistics, directory depth analysis, size distribution
- 🔍 **Search & Filter**: Find files by name, path, extension, or MIME type
- ⚡ **Efficient Sync**: Hash-based change detection for minimal downloads

### 🗳️ Vote System
- 🌐 **Multi-Site Integration**: Support for multiple voting sites
- 🎯 **Reward Management**: Automatic reward distribution
- 📈 **Vote Tracking**: Comprehensive voting statistics and history
- 🔗 **Callback Handling**: Secure webhook processing

### 💻 Client Management
- 🖥️ **Multi-Platform**: Windows, macOS, Linux client distribution
- 📋 **Version Control**: Automatic version management and updates
- 📱 **Download Portal**: User-friendly client download interface

### 🎨 Admin Dashboard
- 📊 **Beautiful Dark Theme**: Professional dark interface with green accents
- 🎭 **Cinzel Font**: Elegant typography for premium feel
- ✨ **Glass Morphism**: Modern UI with backdrop blur effects
- 📱 **Responsive Design**: Works on desktop and mobile
- 📈 **Real-time Stats**: Live revenue, cache, and system monitoring

## 📋 Installation

1. **Clone and Setup**
   ```bash
   git clone <repository-url>
   cd rsps-complete-system
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

## 📚 API Documentation

### 🔐 Authentication

All server endpoints require `Authorization: Bearer <RSPS_SERVER_KEY>` header.

### 🛒 Donation API

#### Create PayPal Checkout
```http
POST /api/checkout
Content-Type: application/json
X-API-Key: your-server-api-key

{
  "player_name": "PlayerName",
  "products": [
    {"product_id": 1, "quantity": 3},
    {"product_id": 2, "quantity": 1}
  ],
  "payment_method": "paypal"
}
```

#### Claim Donations
```http
GET /api/claim/PlayerName
X-API-Key: your-server-api-key
```

#### Get Products
```http
GET /api/products
X-API-Key: your-server-api-key
```

### 📦 Cache Management API

#### Get Cache Manifest with Directory Structure
```http
GET /api/cache/manifest
```

Response includes complete directory tree and file metadata:
```json
{
  "version": "20241011154332",
  "total_files": 150,
  "total_directories": 25,
  "structure": {
    "preserve_paths": true,
    "directory_tree": [...],
    "flat_files": [...]
  },
  "metadata": {
    "format_version": "2.0",
    "supports_directory_structure": true
  }
}
```

#### Download Cache Bundle with Structure
```http
# Download all files with directory structure preserved
GET /api/cache/download?mode=full&preserve_structure=true

# Download specific directory
GET /api/cache/download?mode=selective&paths=models/weapons

# Download specific files (flattened)
GET /api/cache/download?mode=selective&files=config.dat,items.dat&preserve_structure=false
```

#### Get Directory Tree
```http
GET /api/cache/directory-tree
```

#### Search Cache Files
```http
GET /api/cache/search?q=weapon&type=file&extension=dat
```

#### Cache Statistics
```http
GET /api/cache/stats
```

#### Download Individual File
```http
# Download file from root
GET /api/cache/file/config.dat

# Download file from specific path
GET /api/cache/file/player.dat?path=models/characters/player.dat
```

### 🗳️ Vote System API

```http
GET  /vote                    # Vote homepage
POST /vote/set-username       # Set voting username
POST /vote/{site}            # Submit vote to specific site
GET  /vote/stats             # Vote statistics
GET  /vote/user-votes        # User vote history
```

### 💻 Client Download API

```http
GET /download/{os}/{version}  # Download client for specific OS/version
GET /manifest.json           # Client version manifest
GET /play                    # Play page with client downloads
```

### 🔗 Webhook Endpoints

- `POST /api/webhooks/paypal` - PayPal payment notifications
- `POST /api/webhooks/coinbase` - Coinbase Commerce notifications

## 🗄️ Database Schema

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

### Cache Files Table
- `id` - Primary key
- `filename` - Original filename
- `relative_path` - Path within directory structure
- `path` - Storage path
- `size` - File size in bytes
- `hash` - SHA256 hash for integrity
- `file_type` - 'file' or 'directory'
- `mime_type` - MIME type for files
- `metadata` - JSON metadata

### Cache Bundles Table
- `id` - Primary key
- `bundle_key` - MD5 of file list & options
- `file_list` - JSON array of included files
- `path` - Path to compressed archive
- `size` - Bundle size in bytes
- `expires_at` - Expiry timestamp

### Vote Sites Table
- `id` - Primary key
- `name` - Site name
- `url` - Voting URL
- `callback_url` - Webhook URL
- `reward_amount` - Vote reward
- `is_active` - Site status

### Clients Table
- `id` - Primary key
- `name` - Client name
- `version` - Version string
- `os` - Operating system
- `download_url` - Download URL
- `file_size` - File size
- `is_active` - Availability status

## 🎛️ Admin Panel

Access the admin panel at `/admin` to:

### 💰 Donation Management
- ✅ Manage products (CRUD operations)
- 📊 View sales dashboard with analytics
- 🔍 Monitor orders with advanced filtering
- 📈 Track revenue and performance metrics

### 📦 Cache Management
- 📁 Upload files and folders with structure preservation
- 🗂️ Browse directory tree with visual hierarchy
- 📊 Monitor cache statistics and usage
- 🔄 Generate and download manifests
- 🗜️ Manage compressed bundles

### 🗳️ Vote Management
- 🌐 Configure voting sites
- 📈 Monitor vote statistics
- 🎁 Manage vote rewards
- 📋 Track voting history

### 💻 Client Management
- 🖥️ Upload client versions for different OS
- 📋 Manage version manifests
- 📊 Track download statistics

### Admin Features
- **Dark Theme**: Professional dark interface with green accents
- **Cinzel Font**: Elegant typography for premium feel
- **Glass Morphism**: Modern UI with backdrop blur effects
- **Responsive Design**: Works on desktop and mobile
- **Real-time Stats**: Live system monitoring

## 🔒 Security Features

- 🔐 **Server API Authentication**: Secure server-to-server communication
- ✅ **Webhook Verification**: PayPal and Coinbase signature validation
- 🛡️ **Price Validation**: Server-side price verification prevents tampering
- 🚦 **Rate Limiting**: Protection against API abuse
- 🔒 **HTTPS Required**: Secure data transmission
- 🔍 **File Integrity**: SHA256 hash verification for all cache files
- 🚫 **Access Control**: Role-based permissions for admin functions

## 🎮 RSPS Integration Example

```java
// Java example for RSPS server
public class RSPSSystemManager {
    private static final String API_URL = "https://yourdomain.com/api";
    private static final String SERVER_KEY = "your_server_key";
    
    // Donation Management
    public String createPayPalCheckout(String username, List<CartItem> items) {
        // Build request with items
        // Send POST to /api/checkout
        // Return redirect URL to player
    }
    
    public List<ClaimableItem> claimDonations(String username) {
        // Send GET to /api/claim/{username}
        // Process returned items
        // Add items to player inventory
    }
    
    // Cache Management
    public void downloadCacheUpdates() {
        // Get manifest from /api/cache/manifest
        // Compare with local cache
        // Download changed files with /api/cache/download
        // Preserve directory structure
    }
    
    // Vote Rewards
    public void processVoteRewards(String username) {
        // Check for pending vote rewards
        // Add rewards to player account
    }
}
```

## 🚀 Development

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

4. **Generate Cache Manifest**
   ```bash
   php artisan cache:generate-manifest
   ```

5. **Clean Up Expired Bundles**
   ```bash
   php artisan cache:cleanup-bundles
   ```

## 🌐 Production Deployment

1. **Environment Setup**
   - Set `APP_ENV=production`
   - Set `APP_DEBUG=false`
   - Configure proper database credentials
   - Set up SSL/HTTPS

2. **Payment Provider Setup**
   - Switch PayPal to live mode
   - Configure production API keys
   - Set up webhook endpoints

3. **Cache Management Setup**
   - Configure storage permissions
   - Set up automated manifest generation
   - Configure bundle cleanup schedule

4. **Security Checklist**
   - Generate strong `RSPS_SERVER_KEY`
   - Enable rate limiting
   - Configure firewall rules
   - Set up monitoring and logging
   - Secure file upload directories

## 📊 Performance Optimization

- **Cache Bundles**: Automatic compression and caching
- **Database Indexing**: Optimized queries for large datasets
- **File Streaming**: Efficient large file downloads
- **Background Jobs**: Async processing for heavy operations
- **CDN Ready**: Static asset optimization

## 🛠️ Maintenance Commands

```bash
# Generate cache manifest
php artisan cache:generate-manifest

# Clean expired bundles
php artisan cache:cleanup-bundles

# Optimize application
php artisan optimize

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## 📞 Support

For issues and questions:
1. Check the logs in `storage/logs/laravel.log`
2. Verify webhook signatures are working
3. Test API endpoints with proper authentication
4. Ensure database migrations are up to date
5. Check cache file permissions and storage space

## 📄 License

This project is open-sourced software licensed under the MIT license.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📈 Roadmap

- [ ] Real-time notifications
- [ ] Advanced analytics dashboard
- [ ] Multi-server support
- [ ] API rate limiting enhancements
- [ ] Mobile admin app
- [ ] Automated backup system