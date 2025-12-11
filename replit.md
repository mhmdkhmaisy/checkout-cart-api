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
- **Authentication & Access Control:**
    - Username/password-based authentication with session management
    - "Remember me" functionality for persistent login sessions
    - Role-based access control using rights levels (0-4)
    - Owner-level access (rights=4) required for admin panel
    - Secure password hashing using Laravel's built-in bcrypt
    - Login throttling protection via Laravel's throttle middleware
    - Public login page at /login (no registration)
    - CSRF protection on all forms
    - Session regeneration on login/logout for security
- **Donation Management:** Integrates with PayPal and Coinbase Commerce. Products can be organized into categories and configured as bundles/packs containing multiple items.
- **Cache File Distribution:**
    - Supports multi-file, folder, and archive uploads (ZIP/TAR) with automatic directory structure preservation.
    - Uses SHA256 for file hashing and duplicate detection.
    - Includes robust security features: directory traversal protection, filename sanitization, and storage isolation.
    - Implements an intelligent delta patch system for efficient, incremental updates using semantic versioning.
    - Optimized storage architecture stores only compressed patch ZIPs permanently, eliminating original upload duplication.
    - Advanced patch management tools include side-by-side comparison, changelog generation, file history tracking, and integrity verification.
- **Multi-site Voting System:** Tracks votes and rewards.
    - Usernames support letters, numbers, underscores, and spaces.
    - Vote claim API endpoint (`GET /api/claimVote/{playerName}`) returns unclaimed votes and marks them as claimed.
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
        - Block-based content editor with drag-and-drop functionality:
            - Standard blocks: header, paragraph, list, code, image, alert
            - Enhanced blocks: callout/highlight (5 color types), table (dynamic rows/columns), separator
            - OSRS-style pixelated header with subheader support (5 color schemes using Press Start 2P font)
            - Nested section components: Patch Notes Section (red-themed) and Custom Section (6 color schemes)
            - Interactive nested block editor: sections contain their own toolbar buttons to add child blocks
            - Scoped drag-and-drop: nested blocks can only be reordered within their parent section
            - Refactored BlockEditor class architecture manages both root and nested editor contexts
            - Recursive serialization/deserialization for saving and loading nested content structures
            - Single-level nesting limitation prevents infinite nesting (sections cannot contain other sections)
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
        - Auto-Fill feature: parses formatted text (headers with **, items with */-) to auto-generate header and list blocks, streamlining content creation for item lists and boss drops
        - Discord webhook integration with robust message ordering:
            - Automatic notification to configured Discord channels on update publish
            - Manual "Notify Client Update" button for client updates with optional role mention
            - Retry mechanism with exponential backoff and Discord rate limit handling
            - Guaranteed message ordering: header → content blocks → footer link
            - Sections render as styled Discord embeds with proper color coding
            - allowed_mentions configuration prevents accidental @everyone/@here pings
    - Displays a tabbed Top Voters Widget (weekly/monthly).
- **Wiki Documentation System:**
    - Comprehensive documentation platform with Dev-Docs style layout
    - Table of Contents with automatic smooth scrolling navigation
    - Rich text editor (TinyMCE) with custom UI components (info boxes, code blocks, alerts, tabs)
    - Category-based organization with uncategorized page support
    - Customizable page icons and ordering
    - Publish/draft status control
    - Public wiki accessible at /wiki/{slug} with sidebar navigation
    - Admin management interface with full CRUD operations
    - Auto-slug generation from titles
    - Responsive design matching dragon theme aesthetics
- **Performance Monitor & Analysis System:**
    - Provides a real-time dashboard for CPU, memory, response times, and disk space.
    - Tracks HTTP requests, logs slow database queries, and monitors queue job analytics.
    - Offers visual analytics via Chart.js and an alert system for performance thresholds.

**System Design Choices:**
- **Database:** SQLite for development; MySQL/PostgreSQL recommended for production.
- **Authentication:** Session-based authentication with web guard, supporting remember me tokens.
- **Access Control:** Middleware-based authorization (auth, owner) protecting all admin routes.
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