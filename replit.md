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
- The administration panel features a dark-themed dashboard.
- The file manager provides cPanel-like directory browsing with breadcrumb navigation.
- Supports drag-and-drop file uploads, folder uploads, and archive extraction with preserved directory structure.
- Public-facing pages and admin interfaces maintain a consistent "dragon theme" color scheme (red, gold, dark backgrounds).
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
- **Public Homepage & Content System:**
    - Features an Events System with full management, status tracking, and display.
    - Includes an Updates/News System with a block-based content editor (header, paragraph, list, code, image, alert) and drag-and-drop functionality.
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