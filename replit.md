# RSPS Complete System - Replit Project

### Overview
This project is a comprehensive Laravel-based system for RuneScape Private Servers. Its primary goal is to provide robust management features for server administrators, including donation processing, cache file distribution, a multi-site voting system, and client management. The system aims to streamline server operations, enhance user experience, and offers an intuitive, dark-themed admin dashboard. Key capabilities include high-performance chunked file uploads, intelligent file management with directory navigation, and secure handling of game assets.

### User Preferences
- Testing locally on PC (not using Replit preview)
- Focus on upload performance optimization
- Using 100MB/s upload connection

### System Architecture
The system is built on the Laravel 10.x framework, utilizing PHP 8.2.23.

**UI/UX Decisions:**
- The administration panel features a dark-themed dashboard with collapsible sidebar sections for better navigation organization.
- The file manager provides cPanel-like directory browsing with breadcrumb navigation.
- Supports drag-and-drop file uploads, folder uploads, and archive extraction with preserved directory structure.
- Public-facing pages and admin interfaces maintain a consistent "dragon theme" color scheme (red, gold, dark backgrounds).
- Update cards display visual badges for pinned posts to highlight important content.
- API documentation has a modern, GitLab-inspired layout with sticky navigation, HTTP method badges, and comprehensive examples.

**Technical Implementations & Feature Specifications:**
- **Donation Management:** Integrates with PayPal and Coinbase Commerce. Products can be organized into categories and configured as bundles/packs containing multiple items.
- **Cache File Distribution:**
    - Supports multi-file, folder, and archive uploads (ZIP/TAR) with automatic directory structure preservation.
    - Uses SHA256 for file hashing and duplicate detection.
    - Includes robust security features: directory traversal protection, filename sanitization, and storage isolation.
    - Implements an intelligent delta patch system for efficient, incremental updates using semantic versioning.
    - Optimized storage architecture stores only compressed patch ZIPs permanently, eliminating original upload duplication.
    - Advanced patch management tools include side-by-side comparison, changelog generation, file history tracking, and integrity verification.
- **Multi-site Voting System:** Tracks votes and rewards.
- **Client Management:** Facilitates distribution and management of game client versions.
- **Deals & Promotions System:**
    - Store gamification through time-based and spend-based promotional campaigns.
    - Supports single-use and recurrent promotion types with configurable rewards.
    - Real-time progress tracking visible on store page with visual progress bars.
    - Per-user claim limits and global campaign caps for controlled distribution.
    - Automatic spend tracking via payment webhook integration (PayPal and Coinbase).
    - Auto-expiry scheduler runs hourly to deactivate time-limited promotions.
    - Admin panel with comprehensive statistics, claim monitoring, and user progress tracking.
    - Full CRUD operations for promotion management with validation and security checks.
- **Public Homepage & Content System:**
    - Features an Events System with full management, status tracking, and display.
    - Includes a comprehensive Updates/News System with:
        - Block-based content editor (header, paragraph, list, code, image, alert) with drag-and-drop functionality
        - Advanced publishing workflow with draft/published states and scheduling capabilities
        - Featured updates and pinned posts for highlighting important announcements (pinned updates appear first on all listings)
        - Hotfix system: allows updates to be attached to parent updates without appearing in main listings, displayed at the end of parent update pages
        - Image uploads stored in public/assets/updates/ for direct access from content blocks
        - SEO optimization with custom excerpts, meta descriptions, and featured images
        - Category and author attribution for organized content management
        - View counter and analytics tracking
        - Public API endpoints (`/api/updates/latest`, `/api/updates`, `/api/updates/{slug}`) with rate limiting
        - Advanced filtering and search capabilities in admin panel
        - Quick-toggle actions for publish/featured/pinned status
        - Auto-slug generation from titles
        - Comprehensive statistics dashboard showing total, published, draft, featured, and pinned counts
        - Status badges in admin panel for pinned, featured, draft, and hotfix updates
        - Update type indicators (Regular/Client Update/Hotfix) in admin interface
    - Displays a tabbed Top Voters Widget (weekly/monthly).
- **Performance Monitor & Analysis System:**
    - Provides a real-time dashboard for CPU, memory, response times, and disk space.
    - Tracks HTTP requests, logs slow database queries, and monitors queue job analytics.
    - Offers visual analytics via Chart.js and an alert system for performance thresholds.

**System Design Choices:**
- **Database:** SQLite for development; MySQL/PostgreSQL recommended for production.
- **Performance Optimization:** PHP upload limits are configured for large files.
- **Queue System:** Laravel queues are used for asynchronous processing of uploads and file manipulations.
- **API Endpoints:** A structured API provides endpoints for donations, cache management, voting, and includes comprehensive documentation.

### External Dependencies
- **Payment Gateways:**
    - PayPal
    - Coinbase Commerce
- **Database:**
    - SQLite
    - MySQL
    - PostgreSQL