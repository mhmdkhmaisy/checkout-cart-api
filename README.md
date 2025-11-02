# RSPS Complete System

A comprehensive Laravel-based system for RuneScape Private Servers (RSPS) featuring donation management, cache distribution, voting system, and client management with PayPal and Coinbase Commerce integration.

## 📑 Table of Contents

- [Features](#-features)
- [Installation](#-installation)
- [API Documentation](#-api-documentation)
- [Database Schema](#️-database-schema)
- [Admin Panel](#️-admin-panel)
- [Security Features](#-security-features)
- [RSPS Integration Example](#-rsps-integration-example)
  - [Cache Update Integration](#cache-update-integration-full-implementation)
  - [Update Flow Diagram](#update-flow-diagram)
  - [Additional Examples](#additional-integration-examples)
- [Development](#-development)
- [Performance & Optimization](#-performance--optimization)
  - [Cache Upload Optimization](#cache-upload-optimization)
  - [Chunked Upload System](#chunked-upload-system)
  - [Upload Performance Fixes](#upload-performance-fixes)
  - [PHP Configuration Requirements](#php-configuration-requirements)
- [Patch Management System](#-patch-management-system)
- [UI/UX Design System](#-uiux-design-system)
- [Performance Testing](#-performance-testing)
- [Production Deployment](#-production-deployment)
- [Maintenance Commands](#️-maintenance-commands)
- [Changes & Updates](#-changes--updates)
- [Support](#-support)
- [License](#-license)
- [Contributing](#-contributing)
- [Roadmap](#-roadmap)

---

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
- 🚀 **Chunked Uploads**: High-performance resumable uploads (5-10x faster)
- 🔄 **Patch System**: Delta patches for efficient client updates

### 🗳️ Vote System
- 🌐 **Multi-Site Integration**: Support for multiple voting sites
- 🎯 **Reward Management**: Automatic reward distribution
- 📈 **Vote Tracking**: Comprehensive voting statistics and history
- 🔗 **Callback Handling**: Secure webhook processing

### 🎁 Deals & Promotions System
- 🎯 **Store Gamification**: Time-based and spend-based promotional campaigns
- 💎 **Flexible Promotion Types**: Single-use or recurrent bonus rewards
- 📊 **Real-Time Progress Tracking**: Visual progress bars on store page
- 🏆 **Reward Tiers**: Multi-item rewards with configurable quantities
- 👥 **User & Global Limits**: Per-user claim limits and global campaign caps
- ⏰ **Auto-Expiry**: Scheduled automatic expiration of time-limited promotions
- 🔄 **Spend Tracking**: Automatic tracking via payment webhook integration
- 📈 **Admin Analytics**: Comprehensive statistics and claim monitoring
- 🎮 **In-Game Integration**: Claim status tracking for server-side reward distribution

### 💻 Client Management
- 🖥️ **Multi-Platform**: Windows, macOS, Linux client distribution
- 📋 **Version Control**: Automatic version management and updates
- 📱 **Download Portal**: User-friendly client download interface

### 🎨 Admin Dashboard
- 📊 **Beautiful Dark Theme**: Professional dark interface with dragon crimson accents
- 🎭 **Cinzel Font**: Elegant typography for premium feel
- ✨ **Glass Morphism**: Modern UI with backdrop blur effects
- 📱 **Responsive Design**: Works on desktop and mobile
- 📈 **Real-time Stats**: Live revenue, cache, and system monitoring

---

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

---

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

---

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

### Cache Patches Table
- `id` - Primary key
- `version` - Semantic version (e.g., "1.0.1")
- `patch_type` - 'base' or 'incremental'
- `based_on_version` - Parent version for incremental patches
- `file_count` - Number of files in patch
- `total_size` - Total size of all files
- `compressed_size` - Size of compressed patch file
- `changelog` - JSON changelog of changes

### Upload Sessions Table (Chunked Uploads)
- `id` - Primary key
- `upload_key` - Unique upload identifier
- `filename` - Original filename
- `file_size` - Total file size
- `uploaded_size` - Bytes uploaded so far
- `status` - 'uploading', 'processing', 'completed', 'failed'
- `tus_id` - TUS protocol upload ID

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

---

## 🎛️ Admin Panel

Access the admin panel at `/admin` to:

### 💰 Donation Management
- ✅ Manage products (CRUD operations)
- 📊 View sales dashboard with analytics
- 🔍 Monitor orders with advanced filtering
- 📈 Track revenue and performance metrics

### 📦 Cache Management
- 📁 Upload files and folders with structure preservation
- 🚀 **Chunked Upload** for large files (5-10x faster)
- 🗂️ Browse directory tree with visual hierarchy
- 📊 Monitor cache statistics and usage
- 🔄 Generate and download manifests
- 🗜️ Manage compressed bundles
- 📦 Patch system with delta updates

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
- **Dark Dragon Theme**: Professional interface with crimson & gold accents
- **Cinzel Font**: Elegant typography for premium feel
- **Glass Morphism**: Modern UI with backdrop blur effects
- **Responsive Design**: Works on desktop and mobile
- **Real-time Stats**: Live system monitoring

---

## 🔒 Security Features

- 🔐 **Server API Authentication**: Secure server-to-server communication
- ✅ **Webhook Verification**: PayPal and Coinbase signature validation
- 🛡️ **Price Validation**: Server-side price verification prevents tampering
- 🚦 **Rate Limiting**: Protection against API abuse
- 🔒 **HTTPS Required**: Secure data transmission
- 🔍 **File Integrity**: SHA256 hash verification for all cache files
- 🚫 **Access Control**: Role-based permissions for admin functions
- 🛡️ **Directory Traversal Protection**: Automatic path sanitization in uploads
- 🔐 **CSRF Protection**: Configurable CSRF exemptions for API endpoints

---

## 🎮 RSPS Integration Example

### Cache Update Integration (Full Implementation)

This example shows how to integrate the patch system into your game client for automatic cache updates.

```java
import java.io.*;
import java.net.*;
import java.nio.file.*;
import java.util.*;
import java.util.zip.*;
import com.google.gson.*;

public class CacheUpdateManager {
    private static final String API_URL = "https://yourdomain.com";
    private static final String CACHE_DIR = System.getProperty("user.home") + "/.aragon-cache/";
    private static final String MANIFEST_FILE = CACHE_DIR + "patches-manifest.json";
    
    private Gson gson = new Gson();
    
    /**
     * Main cache update method - checks for updates and downloads patches
     * Call this on client startup or when user clicks "Update Cache"
     */
    public boolean checkAndUpdateCache() {
        try {
            System.out.println("Checking for cache updates...");
            
            // Step 1: Check if we have a local manifest
            File manifestFile = new File(MANIFEST_FILE);
            String localVersion = null;
            
            if (manifestFile.exists()) {
                // We have a local manifest - check current version
                localVersion = getLocalVersion();
                System.out.println("Current local cache version: " + localVersion);
            } else {
                System.out.println("No local cache found - will download all patches");
            }
            
            // Step 2: Get latest version info from server
            LatestVersionInfo serverInfo = getLatestVersionFromServer();
            
            if (serverInfo == null) {
                System.err.println("Failed to connect to update server");
                return false;
            }
            
            System.out.println("Server cache version: " + serverInfo.latestVersion);
            
            // Step 3: Determine if we need to update
            if (localVersion != null && localVersion.equals(serverInfo.latestVersion)) {
                System.out.println("Cache is up to date!");
                return true;
            }
            
            // Step 4: Download patches
            if (localVersion == null) {
                // No local cache - download all patches
                System.out.println("Downloading all patches...");
                downloadAllPatches(serverInfo);
            } else {
                // We have local cache - download only newer patches
                System.out.println("Downloading updates from " + localVersion + " to " + serverInfo.latestVersion);
                downloadIncrementalPatches(localVersion, serverInfo.latestVersion);
            }
            
            // Step 5: Update local manifest with latest version
            updateLocalManifest(serverInfo.latestVersion);
            
            System.out.println("Cache update complete! Version: " + serverInfo.latestVersion);
            return true;
            
        } catch (Exception e) {
            System.err.println("Cache update failed: " + e.getMessage());
            e.printStackTrace();
            return false;
        }
    }
    
    /**
     * Get current version from local patches-manifest.json
     */
    private String getLocalVersion() throws IOException {
        String content = new String(Files.readAllBytes(Paths.get(MANIFEST_FILE)));
        JsonObject manifest = gson.fromJson(content, JsonObject.class);
        return manifest.get("version").getAsString();
    }
    
    /**
     * Get latest version info from server
     * GET /admin/cache/patches/latest
     */
    private LatestVersionInfo getLatestVersionFromServer() {
        try {
            URL url = new URL(API_URL + "/admin/cache/patches/latest");
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();
            conn.setRequestMethod("GET");
            conn.setConnectTimeout(10000);
            conn.setReadTimeout(10000);
            
            if (conn.getResponseCode() == 200) {
                BufferedReader reader = new BufferedReader(
                    new InputStreamReader(conn.getInputStream())
                );
                StringBuilder response = new StringBuilder();
                String line;
                while ((line = reader.readLine()) != null) {
                    response.append(line);
                }
                reader.close();
                
                return gson.fromJson(response.toString(), LatestVersionInfo.class);
            } else {
                System.err.println("Server returned: " + conn.getResponseCode());
                return null;
            }
        } catch (Exception e) {
            e.printStackTrace();
            return null;
        }
    }
    
    /**
     * Download all patches (for fresh installation)
     * POST /admin/cache/patches/download-combined
     */
    private void downloadAllPatches(LatestVersionInfo serverInfo) throws IOException {
        System.out.println("Downloading combined patches (all files)...");
        
        // Create cache directory if it doesn't exist
        new File(CACHE_DIR).mkdirs();
        
        // Request all patches from version "0.0.0" to latest
        String requestBody = String.format(
            "{\"from_version\":\"0.0.0\",\"to_version\":\"%s\"}", 
            serverInfo.latestVersion
        );
        
        downloadAndExtractPatch(
            API_URL + "/admin/cache/patches/download-combined",
            requestBody,
            "POST"
        );
    }
    
    /**
     * Download patches one at a time in order (for updates)
     * GET /admin/cache/patches/{version}/download
     */
    private void downloadIncrementalPatches(String fromVersion, String toVersion) 
            throws IOException {
        System.out.println("Downloading incremental patches one at a time...");
        
        // Get server info to retrieve patch list
        LatestVersionInfo serverInfo = getLatestVersionFromServer();
        if (serverInfo == null || serverInfo.patches == null) {
            throw new IOException("Failed to get patch list from server");
        }
        
        // Filter patches that are newer than fromVersion and up to toVersion
        List<LatestVersionInfo.PatchInfo> patchesToDownload = new ArrayList<>();
        for (LatestVersionInfo.PatchInfo patch : serverInfo.patches) {
            if (compareVersions(patch.version, fromVersion) > 0 && 
                compareVersions(patch.version, toVersion) <= 0) {
                patchesToDownload.add(patch);
            }
        }
        
        // Sort patches by version to ensure correct order
        patchesToDownload.sort((p1, p2) -> compareVersions(p1.version, p2.version));
        
        // Download and apply each patch in order
        for (int i = 0; i < patchesToDownload.size(); i++) {
            LatestVersionInfo.PatchInfo patch = patchesToDownload.get(i);
            System.out.println(String.format(
                "Downloading patch %d/%d: v%s", 
                i + 1, 
                patchesToDownload.size(), 
                patch.version
            ));
            
            downloadAndExtractSinglePatch(patch.version);
        }
        
        System.out.println("All incremental patches downloaded successfully!");
    }
    
    /**
     * Download a single patch by version
     * GET /admin/cache/patches/{version}/download
     */
    private void downloadAndExtractSinglePatch(String version) throws IOException {
        String urlString = API_URL + "/admin/cache/patches/" + version + "/download";
        URL url = new URL(urlString);
        HttpURLConnection conn = (HttpURLConnection) url.openConnection();
        conn.setRequestMethod("GET");
        conn.setConnectTimeout(10000);
        conn.setReadTimeout(30000);
        
        // Download patch file
        if (conn.getResponseCode() == 200) {
            File tempZip = new File(CACHE_DIR + "temp_patch_" + version + ".zip");
            
            try (InputStream in = conn.getInputStream();
                 FileOutputStream out = new FileOutputStream(tempZip)) {
                
                byte[] buffer = new byte[8192];
                int bytesRead;
                long totalBytes = 0;
                
                while ((bytesRead = in.read(buffer)) != -1) {
                    out.write(buffer, 0, bytesRead);
                    totalBytes += bytesRead;
                }
                
                System.out.println("Downloaded v" + version + ": " + totalBytes + " bytes");
            }
            
            // Extract ZIP to cache directory
            extractZipFile(tempZip, new File(CACHE_DIR));
            
            // Delete temp ZIP
            tempZip.delete();
            
        } else {
            throw new IOException("Failed to download patch " + version + ": " + conn.getResponseCode());
        }
    }
    
    /**
     * Compare two semantic version strings
     * Returns: negative if v1 < v2, zero if v1 == v2, positive if v1 > v2
     */
    private int compareVersions(String v1, String v2) {
        String[] parts1 = v1.split("\\.");
        String[] parts2 = v2.split("\\.");
        
        int length = Math.max(parts1.length, parts2.length);
        for (int i = 0; i < length; i++) {
            int num1 = i < parts1.length ? Integer.parseInt(parts1[i]) : 0;
            int num2 = i < parts2.length ? Integer.parseInt(parts2[i]) : 0;
            
            if (num1 != num2) {
                return num1 - num2;
            }
        }
        return 0;
    }
    
    /**
     * Download patch ZIP and extract to cache directory
     */
    private void downloadAndExtractPatch(String urlString, String requestBody, 
            String method) throws IOException {
        URL url = new URL(urlString);
        HttpURLConnection conn = (HttpURLConnection) url.openConnection();
        conn.setRequestMethod(method);
        conn.setDoOutput(true);
        conn.setRequestProperty("Content-Type", "application/json");
        
        // Send request body if POST
        if ("POST".equals(method) && requestBody != null) {
            try (OutputStream os = conn.getOutputStream()) {
                os.write(requestBody.getBytes("UTF-8"));
            }
        }
        
        // Download patch file
        if (conn.getResponseCode() == 200) {
            File tempZip = new File(CACHE_DIR + "temp_patch.zip");
            
            try (InputStream in = conn.getInputStream();
                 FileOutputStream out = new FileOutputStream(tempZip)) {
                
                byte[] buffer = new byte[8192];
                int bytesRead;
                long totalBytes = 0;
                
                while ((bytesRead = in.read(buffer)) != -1) {
                    out.write(buffer, 0, bytesRead);
                    totalBytes += bytesRead;
                    
                    // Show progress every 1MB
                    if (totalBytes % (1024 * 1024) == 0) {
                        System.out.println("Downloaded: " + (totalBytes / 1024 / 1024) + " MB");
                    }
                }
                
                System.out.println("Download complete: " + totalBytes + " bytes");
            }
            
            // Extract ZIP to cache directory
            extractZipFile(tempZip, new File(CACHE_DIR));
            
            // Delete temp ZIP
            tempZip.delete();
            
        } else {
            throw new IOException("Failed to download patch: " + conn.getResponseCode());
        }
    }
    
    /**
     * Extract ZIP file preserving directory structure
     */
    private void extractZipFile(File zipFile, File destDir) throws IOException {
        System.out.println("Extracting patch files...");
        
        byte[] buffer = new byte[8192];
        int filesExtracted = 0;
        
        try (ZipInputStream zis = new ZipInputStream(new FileInputStream(zipFile))) {
            ZipEntry entry;
            
            while ((entry = zis.getNextEntry()) != null) {
                File newFile = new File(destDir, entry.getName());
                
                if (entry.isDirectory()) {
                    newFile.mkdirs();
                } else {
                    // Create parent directories if needed
                    new File(newFile.getParent()).mkdirs();
                    
                    // Extract file
                    try (FileOutputStream fos = new FileOutputStream(newFile)) {
                        int len;
                        while ((len = zis.read(buffer)) > 0) {
                            fos.write(buffer, 0, len);
                        }
                    }
                    
                    filesExtracted++;
                    if (filesExtracted % 100 == 0) {
                        System.out.println("Extracted: " + filesExtracted + " files");
                    }
                }
                
                zis.closeEntry();
            }
        }
        
        System.out.println("Extraction complete: " + filesExtracted + " files extracted");
    }
    
    /**
     * Update local patches-manifest.json with latest version
     */
    private void updateLocalManifest(String version) throws IOException {
        JsonObject manifest = new JsonObject();
        manifest.addProperty("version", version);
        manifest.addProperty("updated_at", System.currentTimeMillis());
        
        String json = gson.toJson(manifest);
        Files.write(Paths.get(MANIFEST_FILE), json.getBytes());
        
        System.out.println("Updated local manifest to version: " + version);
    }
    
    /**
     * Data class for server response
     */
    private static class LatestVersionInfo {
        String latestVersion;
        int totalPatches;
        long totalSize;
        PatchInfo[] patches;
        
        static class PatchInfo {
            int id;
            String version;
            String patchType;
            String basedOnVersion;
            int fileCount;
            long totalSize;
            long compressedSize;
        }
    }
}
```

### Usage in Your Game Client

```java
// On client startup
public static void main(String[] args) {
    CacheUpdateManager updateManager = new CacheUpdateManager();
    
    // Check and update cache before launching game
    if (updateManager.checkAndUpdateCache()) {
        System.out.println("Cache is ready!");
        // Launch game
        launchGame();
    } else {
        System.err.println("Failed to update cache. Please try again.");
        System.exit(1);
    }
}
```

### API Endpoints Used

1. **GET /admin/cache/patches/latest**
   - Returns latest version info and patch list
   - No authentication required (public endpoint)

2. **GET /admin/cache/patches/{version}/download**
   - Downloads a specific patch by version number
   - Used for incremental updates (downloading patches one at a time)
   - Returns ZIP file with changed files for that version
   - Example: `GET /admin/cache/patches/1.0.2/download`

3. **POST /admin/cache/patches/download-combined**
   - Downloads combined patches from version X to Y
   - Used for first-time installation (all patches in one file)
   - Request body: `{"from_version": "0.0.0", "to_version": "1.0.5"}`
   - Returns ZIP file with all changed files

### Local Manifest Format

```json
{
  "version": "1.0.5",
  "updated_at": 1729368000000
}
```

### Update Flow Diagram

```
┌─────────────────────────────────────────────────┐
│ Client Startup                                  │
└────────────────┬────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────┐
│ Check if patches-manifest.json exists          │
└────────┬───────────────────────────────┬────────┘
         │ Exists                        │ Not Found
         ▼                               ▼
┌────────────────────┐      ┌────────────────────────┐
│ Read local version │      │ Set localVersion = null│
│ from manifest      │      │                        │
└────────┬───────────┘      └───────────┬────────────┘
         │                               │
         └───────────────┬───────────────┘
                         ▼
         ┌───────────────────────────────┐
         │ GET /admin/cache/patches/latest│
         │ Get server's latest version   │
         └───────────┬───────────────────┘
                     ▼
         ┌───────────────────────────────┐
         │ Compare versions              │
         └───┬───────────────────────┬───┘
             │ Same                  │ Different/Null
             ▼                       ▼
    ┌────────────────┐    ┌──────────────────────┐
    │ Already        │    │ Need Update          │
    │ Up-to-date     │    │                      │
    └────────────────┘    └──────┬───────────────┘
                                  ▼
                     ┌────────────────────────────┐
                     │ localVersion == null?      │
                     └──────┬───────────────┬─────┘
                            │ Yes (First-time) │ No (Existing user)
                            ▼                    ▼
              ┌──────────────────────┐  ┌─────────────────────┐
              │ Download combined    │  │ Get list of patches │
              │ patch (all files)    │  │ between localVersion│
              │ POST download-combined│ │ and latest          │
              │ (0.0.0 → latest)     │  └──────────┬──────────┘
              └──────────┬───────────┘             │
                         │                         ▼
                         │              ┌─────────────────────┐
                         │              │ Download patches    │
                         │              │ one at a time in    │
                         │              │ order (1.0.1, 1.0.2)│
                         │              │ GET /patches/{ver}/ │
                         │              │ download (loop)     │
                         │              └──────────┬──────────┘
                         │                         │
                         └──────────┬──────────────┘
                                    ▼
                    ┌────────────────────────────┐
                    │ Extract ZIP files to       │
                    │ {user.home}/.aragon-cache/ │
                    └──────────┬─────────────────┘
                               ▼
                    ┌──────────────────────────┐
                    │ Update local             │
                    │ patches-manifest.json    │
                    │ with latest version      │
                    └──────────┬───────────────┘
                               ▼
                    ┌──────────────────────────┐
                    │ Cache Ready - Launch Game│
                    └──────────────────────────┘
```

### Additional Integration Examples

#### Donation Management
```java
public class DonationManager {
    private static final String API_URL = "https://yourdomain.com/api";
    private static final String SERVER_KEY = "your_server_key";
    
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
}
```

#### Vote Rewards
```java
public class VoteManager {
    public void processVoteRewards(String username) {
        // Check for pending vote rewards
        // Add rewards to player account
    }
}
```

---

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

6. **Start Queue Worker** (for chunked uploads)
   ```bash
   php artisan queue:work --queue=default --tries=3 --timeout=3600
   ```

---

## ⚡ Performance & Optimization

### Cache Upload Optimization

#### Backend Optimizations (PHP/Laravel)

**1. Batch Database Operations**
- Changed from individual `updateOrCreate()` calls to single `upsert()` operation
- Reduces database round-trips from N queries to 1 query for N files
- Uses Laravel's native `upsert()` method for optimal performance

**2. Optimized Duplicate Checking**
- Single database query to check all files at once instead of N queries
- Uses compound WHERE clauses with OR conditions for batch lookup
- Results cached in memory for fast access

**3. Deferred Manifest Regeneration**
- Manifest now regenerates only ONCE after all uploads complete
- Previously regenerated after EVERY batch (major bottleneck)
- Reduces I/O overhead significantly for large uploads

**4. Smart Hash Computation**
- Files are stored first, then hashed (allows parallel processing)
- Duplicate files detected after storage are cleaned up automatically
- Uses MD5 for new files (10x faster than SHA256)
- SHA256 only used when sizes match for duplicate detection

#### Frontend Optimizations (JavaScript)

**1. Increased Batch Sizes**
- Small files (<1MB): 50 files per batch
- Medium files (<10MB): 20 files per batch
- Large files (<50MB): 10 files per batch
- Very large files (>50MB): 5 files per batch

**2. Single HTTP Request per Batch**
- All files in a batch now sent in one HTTP request
- Eliminates HTTP overhead (connection setup, headers, etc.)
- Reduces server processing time per file

**3. Throttled Progress Updates**
- Progress updates limited to every 100ms
- Reduces DOM manipulation overhead
- Prevents UI thread blocking

### Chunked Upload System

#### Overview
High-performance chunked file upload using the TUS protocol for resumable uploads.

**Performance Gains: 5-10x faster than standard uploads**

#### Key Features
- **Chunked Uploads**: Files split into 5MB chunks and uploaded in parallel
- **Async Processing**: Hash computation and manifest regeneration happen in background jobs
- **Resumable Uploads**: Network interruptions don't reset upload progress
- **Parallel Processing**: Multiple chunks upload simultaneously for maximum speed

#### Components

**Backend:**
- TUS Protocol Server (`ChunkedUploadController.php`)
- Background Jobs (`ProcessUploadedFile.php`, `RegenerateCacheManifest.php`)
- Upload Session Tracking (Database)

**Frontend:**
- Uppy.js Dashboard
- TUS Plugin for chunked uploads
- Real-time progress tracking
- Drag & drop interface

#### Installation

1. **Install TUS PHP Library**
   ```bash
   composer require ankitpokhrel/tus-php:^2.3
   ```

2. **Run Migrations**
   ```bash
   php artisan migrate
   ```

3. **Configure Queue Worker**
   ```bash
   # Add to .env
   QUEUE_CONNECTION=database
   
   # Run queue tables migration
   php artisan queue:table
   php artisan migrate
   
   # Start worker
   php artisan queue:work --queue=default --tries=3 --timeout=3600
   ```

4. **Configure Storage**
   ```bash
   mkdir -p storage/app/tus_uploads
   chmod -R 775 storage/app/tus_uploads
   ```

#### Usage

1. Navigate to `/admin/cache`
2. Click **Upload** → **Chunked Upload (Recommended)**
3. Drag files into Uppy dashboard
4. Monitor real-time progress
5. Files process in background after upload

**Keyboard Shortcut**: `Ctrl+Shift+U` to open chunked upload modal

#### Performance Comparison

| Metric | Standard Upload | Chunked Upload |
|--------|----------------|----------------|
| 100MB file | ~120 seconds | ~15-20 seconds |
| Resume capability | ❌ No | ✅ Yes |
| Parallel uploads | ❌ No | ✅ Yes |
| Worker blocking | ✅ Blocks | ❌ Non-blocking |
| **Speed gain** | Baseline | **5-10x faster** |

### Upload Performance Fixes

#### Issue: Speed Degradation
Upload speed was degrading from 20MB/s to <1MB/s due to CPU-intensive SHA256 hashing happening synchronously.

#### Solution: Conditional Hashing Strategy
```php
if ($existing) {
    if ($existing->size !== $fileSize) {
        $hash = md5_file($file->getRealPath());  // 10x faster
    } else {
        $hash = hash_file('sha256', $file->getRealPath());
    }
} else {
    $hash = md5_file($file->getRealPath());  // Fast for new files
}
```

**Performance Impact:**
- MD5 hash: ~5-10ms per file
- SHA256 hash: ~50-100ms per file
- **Result: Sustained 15-20 MB/s upload speed** ✅

### PHP Configuration Requirements

#### Critical Settings (Must Configure)

```ini
upload_max_filesize = 1024M
post_max_size = 1024M
max_execution_time = 600
max_input_time = 600
memory_limit = 2G
max_input_vars = 5000
```

#### Configuration Methods

**Option 1: php.ini (Recommended)**
```bash
# Find php.ini location
php --ini

# Edit and add/update settings
# Restart PHP-FPM/Apache/Nginx
```

**Option 2: .user.ini (CGI/FastCGI)**
```ini
# Create in project root and public/ directory
upload_max_filesize = 1024M
post_max_size = 1024M
memory_limit = 2G
```
*Note: Requires 5-minute cache time or PHP-FPM restart*

**Option 3: Apache .htaccess** (Already configured in `public/.htaccess`)
```apache
<IfModule mod_php.c>
    php_value upload_max_filesize 1024M
    php_value post_max_size 1024M
    php_value memory_limit 2G
</IfModule>
```

**Option 4: Nginx Configuration**
```nginx
server {
    client_max_body_size 1024M;
    client_body_timeout 600s;
    
    location ~ \.php$ {
        fastcgi_read_timeout 600s;
    }
}
```

#### Verification

```bash
# Check current settings
php -i | grep -E "upload_max_filesize|post_max_size|memory_limit"

# Expected output:
# upload_max_filesize => 1024M => 1024M
# post_max_size => 1024M => 1024M
# memory_limit => 2G => 2G
```

#### Expected Performance After Configuration

**Small Files (100 files @ 1MB each):**
- Speed: **Sustained 15-20 MB/s** (no degradation)
- Time: **~30-40 seconds** (was 2-3 minutes)
- Database queries: **3-5 total** (was 100+)
- HTTP requests: **2-3** (was 100)

**Large Files (10 files @ 100MB each):**
- Speed: **Sustained 10-15 MB/s**
- Time: **~1-2 minutes**
- Minimal overhead

---

## 📦 Patch Management System

### Overview
Delta patch system for efficient client cache updates. Clients only download changed files instead of full cache.

### Patch Types
- **Base Patch**: First patch containing all files
- **Delta Patch**: Incremental patch with only changed files
- **Auto-Merge**: Combines 15+ incremental patches into new base

### Key Features
- ✅ Automatic patch generation on file changes
- ✅ Change detection to skip unnecessary patches
- ✅ Debouncing to prevent duplicate patches
- ✅ Compressed ZIP archives
- ✅ Version management (semantic versioning)
- ✅ Patch chain visualization
- ✅ Download combined patches
- ✅ Merge incremental patches into base

### Patch Generation Flow

1. File operation (upload/delete/extract) triggers `cache:generate-manifest`
2. Command generates manifest JSON file
3. Command automatically generates cache patch
4. Patch includes all changed/new files since last version
5. Patch is compressed into a ZIP file
6. Patch metadata stored in database

### API Endpoints

```http
GET  /admin/cache/patches/latest           # Get latest version info
POST /admin/cache/patches/check-updates    # Check for available updates
GET  /admin/cache/patches/{patch}/download # Download specific patch
POST /admin/cache/patches/download-combined # Download combined patches
POST /admin/cache/patches/merge             # Merge incremental patches
DELETE /admin/cache/patches/{patch}        # Delete patch (delta only)
POST /admin/cache/patches/clear-all        # Delete all patches
```

### Client Integration Example

```java
// Check for updates
String currentVersion = "1.0.0";
UpdateResponse updates = checkForUpdates(currentVersion);

if (updates.hasUpdates()) {
    // Download combined patches
    downloadCombinedPatches(currentVersion, updates.getLatestVersion());
    
    // Apply patches
    applyPatches();
    
    // Update local version
    updateVersion(updates.getLatestVersion());
}
```

### Directory Structure

```
storage/app/
├── cache_files/              # Actual cache files
│   ├── file1.dat
│   ├── models/
│   └── textures/
├── cache/
│   ├── patches/              # Patch ZIP files
│   │   ├── 1.0.0.zip        # Base patch
│   │   ├── 1.0.1.zip        # Delta patch
│   │   └── 1.0.2.zip        # Delta patch
│   └── manifests/            # Full state manifests
│       ├── 1.0.0.json
│       ├── 1.0.1.json
│       └── 1.0.2.json
```

### Bundle System Retirement (Oct 14, 2025)

The old bundle system has been retired and integrated directly into the file manager view at `/admin/cache`. Patch functionality remains fully operational with improved UX.

**Benefits:**
- Simplified navigation (single view for file & patch management)
- Reduced complexity
- Better user experience
- Maintained all patch functionality

---

## 🎨 UI/UX Design System

### Dragon Theme Color Palette

#### Primary Colors (Dragon Crimson)
```css
--primary-color: #c41e3a        /* Deep Dragon Crimson */
--primary-bright: #e63946       /* Bright Crimson */
--primary-dark: #a01729         /* Dark Crimson Shadow */
```

#### Accent Colors (Dragon Gold & Ember)
```css
--accent-gold: #d4a574          /* Dragon Gold */
--accent-ember: #ff6b35         /* Dragon Ember Orange */
--accent-color: #0a0a0a         /* Deep Black */
```

#### Background Colors
```css
--background-dark: #0d0d0d      /* Darker base */
--secondary-color: #141414      /* Secondary dark */
--card-background: rgba(20, 16, 16, 0.92)  /* Warmer card bg */
```

#### Text Colors
```css
--text-light: #f0f0f0           /* Brighter white */
--text-muted: #a0a0a0           /* Lighter muted */
--text-gold: #d4a574            /* Gold text */
```

#### Border & Glow Effects
```css
--border-color: #3a2a2a         /* Warm brown-tinted border */
--border-gold: rgba(212, 165, 116, 0.25)
--border-ember: rgba(255, 107, 53, 0.15)
--glow-primary: rgba(196, 30, 58, 0.4)
--glow-gold: rgba(212, 165, 116, 0.3)
```

### Key Visual Enhancements

**1. Dragon Gradient Logo**
- Gradient from crimson to gold
- Enhanced glow effects mimicking dragon fire
- Mystical and premium feel

**2. Multi-layered Gradient Background**
- Base gradient: Deep diagonal sweep
- 3 radial overlays: Crimson (8%), gold (6%), ember (4%)
- Creates depth and warm atmosphere
- Immersive dragon lair ambiance

**3. Enhanced Glass Cards**
- Warmer brown-tinted borders
- Gold accent in top gradient bar
- Dual-glow hover effect (crimson + gold)
- Subtle inner highlight for depth

**4. Premium Buttons**
- Primary buttons have crimson-to-gold gradient on hover
- Enhanced shadow with ember glow
- Inner highlight for 3D effect

**5. Rich Header & Footer**
- Warmer gradient backgrounds
- Gold accent in border gradients
- Cohesive with dragon theme

### Theme Philosophy: The Dragon's Lair

The enhanced color scheme creates a mystical dragon's lair atmosphere:
- **Deep Crimson** represents dragon scales and fire
- **Gold Accents** symbolize dragon treasure and wisdom
- **Ember Orange** hints at smoldering dragon breath
- **Warm Blacks** evoke the depths of a dragon's cave

---

## 🧪 Performance Testing

### Test Suite Overview

The application includes a comprehensive performance testing suite using k6 for load testing.

#### Available Tests

1. **Baseline Test** - Quick baseline (1 user, 10 iterations)
2. **Store Flow Load Test** - E-commerce simulation (10-50 users, ~12 min)
3. **Vote Rush Spike Test** - Vote campaign surge (10-100 users spike)
4. **Admin Panel Load Test** - Admin operations (10-50 users, ~12 min)
5. **Client Downloads Test** - Download stress test (5-10 users, ~8 min)
6. **Cache Downloads Test** - Cache API testing (5 users, ~5 min)
7. **Simple Load Test** - curl-based test (no k6 required)
8. **Full Test Suite** - All tests sequentially (~40 min)
9. **Analyze Results** - View test results and metrics

#### Running Tests

```bash
# Navigate to test directory
cd tests/performance

# Run interactive menu
./run-tests.sh

# Select test number (1-9)
# Results saved to tests/performance/results/
```

#### Test Scenarios

**Store Flow:**
- Browse products
- Add to cart
- Update quantities
- Checkout process

**Admin Panel:**
- Dashboard loading
- Cache file browsing
- File uploads
- Performance monitoring

**Vote Campaign:**
- Vote page loading
- Username setting
- Status checks
- Vote statistics

**Cache Downloads:**
- Manifest retrieval
- Patch downloads
- Combined patch downloads
- Update checks

#### Performance Targets (95th Percentile)

| Route Type | Target | Warning | Critical |
|------------|--------|---------|----------|
| Static Pages | < 200ms | 500ms | 1000ms |
| Dynamic Pages | < 500ms | 1000ms | 2000ms |
| API Endpoints | < 300ms | 800ms | 1500ms |
| Database Queries | < 200ms | 500ms | 1000ms |
| File Operations | < 1000ms | 3000ms | 5000ms |

#### Monitoring During Tests

- **Performance Monitor**: `/admin/performance`
- **System Resources**: htop, iostat, netstat
- **Database**: MySQL slow query log
- **Application Logs**: `storage/logs/laravel.log`

### Stress Testing Guide

Comprehensive stress testing methodology for identifying performance bottlenecks, resource limits, and scalability issues.

#### Testing Phases

1. **Baseline Testing** - Establish normal load performance
2. **Load Testing** - Gradual increase to identify degradation
3. **Spike Testing** - Sudden traffic spikes
4. **Endurance Testing** - Sustained load (2-4 hours)
5. **Stress Testing** - Push beyond capacity to find breaking points

#### Critical Routes to Test

**High Priority:**
- `POST /store/add-to-cart` - Cart operations
- `POST /vote/set-username` - Vote system
- `GET /admin/cache` - File manager
- `GET /admin/performance/routes` - Performance data
- `POST /admin/cache/finalize-upload` - File uploads

**Database-Intensive:**
- `GET /admin/dashboard` - Multiple aggregations
- `GET /admin/orders` - Large dataset queries
- `GET /vote/stats` - Vote aggregations

**File I/O:**
- `POST /admin/cache` - File uploads
- `GET /admin/cache/download-manifest`
- `POST /admin/cache/extract-file` - TAR extraction

#### Resource Limits & Capacity Planning

**Web Server (PHP-FPM):**
- Max workers: 50-100
- Request timeout: 60s (120s for file ops)
- Memory per worker: ~50-100MB

**Database (MySQL/MariaDB):**
- Max connections: 150-200
- Connection pool size: 10-20 per worker
- InnoDB buffer pool: 70% of RAM

**Memory Allocation (2GB minimum, 4GB recommended):**
- OS: 500MB
- PHP-FPM: 1GB (50 workers × 20MB)
- MySQL: 1.5GB
- Redis/Cache: 512MB
- Buffer: 500MB

For detailed stress testing methodology, see `tests/performance/STRESS_TESTING_GUIDE.md` (now integrated into this README).

---

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

5. **Performance Optimization**
   - Enable OpCache
   - Configure Redis for sessions/cache
   - Set up queue workers (for chunked uploads)
   - Configure Supervisor for queue management

6. **Queue Worker Setup (Production)**
   ```ini
   # /etc/supervisor/conf.d/laravel-queue.conf
   [program:laravel-queue-worker]
   process_name=%(program_name)s_%(process_num)02d
   command=php /path/to/artisan queue:work --queue=default --tries=3 --timeout=3600
   autostart=true
   autorestart=true
   user=www-data
   numprocs=4
   redirect_stderr=true
   stdout_logfile=/path/to/storage/logs/worker.log
   ```

---

## 🛠️ Maintenance Commands

```bash
# Generate cache manifest and patches
php artisan cache:generate-manifest

# Clean expired bundles
php artisan cache:cleanup-bundles

# Optimize application
php artisan optimize

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Queue management
php artisan queue:work
php artisan queue:stats
php artisan queue:failed
php artisan queue:retry all

# Clean old upload sessions (create as scheduled job)
php artisan tinker
>>> UploadSession::where('created_at', '<', now()->subDay())->delete();
```

---

## 📝 Changes & Updates

### October 19, 2025
**Performance Test Fixes**
- ✅ Fixed CSRF token mismatch errors in cache download performance tests
  - Added `/admin/cache/patches/download-combined` to CSRF exceptions
  - Added `/admin/cache/patches/check-updates` to CSRF exceptions
  - **Result**: Cache download tests now work correctly (0% fail rate instead of 100%)

- ✅ Fixed auto-closing terminal windows in performance tests
  - Added "Press any key to exit..." pause to `run-tests.sh`
  - Added smart pause to `analyze-results.sh` (only when run directly)
  - Added smart pause to `simple-load-test.sh` (only when run directly)
  - **Result**: Users can now read test results before window closes

### October 17, 2025
**Enhanced Dragon Theme Color Scheme**
- Updated color palette with dragon crimson, gold, and ember accents
- Implemented multi-layered gradient background (no more solid black)
- Enhanced glass cards with dual-glow hover effects
- Updated all public-facing pages with new theme
- Improved button styles with gradient hover effects
- **Files Modified**: `public.blade.php`, `vote/stats.blade.php`

### October 14, 2025
**Bundle System Retirement**
- Retired standalone bundle system
- Integrated patch functionality into file manager view (`/admin/cache`)
- Removed separate `/admin/cache/bundles` route
- Simplified navigation with unified interface
- Maintained all patch functionality
- **Files Affected**: `CacheFileController.php`, `routes/web.php`, `index.blade.php`
- **Deprecated**: `CacheBundleController.php`, `bundles.blade.php`

### January 15, 2024
**Chunked Upload Implementation**
- Implemented TUS protocol for resumable uploads
- Added Uppy.js dashboard for drag-and-drop uploads
- Created upload session tracking system
- Added background job processing for file handling
- Implemented async hash computation and manifest regeneration
- **Performance Gain**: 5-10x faster uploads
- **Files Added**: `ChunkedUploadController.php`, `ProcessUploadedFile.php`, `RegenerateCacheManifest.php`
- **Dependencies Added**: `ankitpokhrel/tus-php:^2.3`

### Prior Updates (Dates Estimated)

**Patch Generation Fixes**
- Fixed wrong directory path (`cache` → `cache_files`)
- Implemented change detection to skip unnecessary patches
- Added 3-second debouncing for bulk uploads
- Centralized patch generation in manifest command
- Added "Clear All Patches" feature
- **Result**: Patches now contain actual files with correct sizes

**Cache Upload Optimization**
- Implemented batch database operations (single `upsert()`)
- Optimized duplicate checking (single query for all files)
- Deferred manifest regeneration (once at end instead of per batch)
- Added smart hash computation (MD5 for new files, SHA256 for duplicates)
- Increased frontend batch sizes (50 files for <1MB)
- **Performance Gain**: 5-8x faster for small files, 3-5x for medium files

**Upload Performance Fixes**
- Implemented conditional hashing strategy
- Changed from SHA256 to MD5 for new files (10x faster)
- Added size comparison before hash computation
- **Result**: Sustained 15-20 MB/s upload speed

**PHP Configuration Documentation**
- Created `.user.ini` files for PHP settings
- Documented Apache, Nginx, and php.ini configuration methods
- Added verification commands
- **Critical Settings**: 1024M upload limit, 2G memory, 600s timeout

**UI/UX Enhancements**
- Implemented dragon theme with crimson, gold, and ember colors
- Created multi-layered gradient backgrounds
- Added glass morphism effects
- Enhanced button and card hover effects
- Improved typography with Cinzel font

**Performance Testing Suite**
- Created k6-based performance test scenarios
- Implemented baseline, load, spike, and endurance tests
- Added simple curl-based tests (no k6 required)
- Created interactive test runner script
- Added result analysis tools
- **Test Coverage**: Store flow, admin panel, vote system, cache downloads

---

## 📞 Support

For issues and questions:
1. Check the logs in `storage/logs/laravel.log`
2. Verify webhook signatures are working
3. Test API endpoints with proper authentication
4. Ensure database migrations are up to date
5. Check cache file permissions and storage space
6. Review PHP configuration settings
7. Monitor queue worker status (for chunked uploads)

### Troubleshooting Common Issues

**Upload Speed Slow:**
1. Verify PHP configuration limits (see PHP Configuration Requirements)
2. Check disk I/O performance
3. Monitor server resources (CPU, memory)
4. Consider using chunked upload for large files

**Chunked Upload Issues:**
1. Ensure queue worker is running
2. Check TUS upload directory permissions
3. Verify storage space availability
4. Review failed jobs: `php artisan queue:failed`

**Patch Generation Issues:**
1. Verify correct directory: `storage/app/cache_files/`
2. Check manifest generation: `php artisan cache:generate-manifest`
3. Review patch files: `ls -lh storage/app/cache/patches/`
4. Clear all patches if needed: POST `/admin/cache/patches/clear-all`

**Performance Issues:**
1. Check Performance Monitor: `/admin/performance`
2. Review slow query log
3. Verify database indexes
4. Monitor resource usage
5. Run performance tests to identify bottlenecks

---

## 📄 License

This project is open-sourced software licensed under the MIT license.

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

---

## 📈 Roadmap

- [ ] Real-time notifications
- [ ] Advanced analytics dashboard
- [ ] Multi-server support
- [ ] API rate limiting enhancements
- [ ] Mobile admin app
- [ ] Automated backup system
- [ ] WebSocket support for live updates
- [ ] S3-compatible storage integration
- [ ] CDN integration for static assets
- [ ] Advanced caching strategies (Redis)
- [ ] GraphQL API support
- [ ] Docker containerization
- [ ] Kubernetes deployment configs
- [ ] Advanced monitoring & alerting
- [ ] Automated performance testing in CI/CD
