# RSPS Complete System - Replit Project

### Overview
This project is a comprehensive Laravel-based system designed for RuneScape Private Servers. Its core purpose is to provide robust management features for server administrators, including donation processing, cache file distribution, a multi-site voting system, and client management. The system aims to streamline server operations, enhance user experience, and provide a dark-themed, intuitive admin dashboard. Key capabilities include high-performance chunked file uploads, intelligent file management with directory navigation, and secure handling of game assets.

### User Preferences
- Testing locally on PC (not using Replit preview)
- Focus on upload performance optimization
- Using 100MB/s upload connection

### System Architecture
The system is built on the Laravel 10.x framework, utilizing PHP 8.2.23.

**UI/UX Decisions:**
- The administration panel features a dark-themed dashboard for improved aesthetics and usability.
- Uppy.js is integrated for a modern, drag-and-drop chunked upload interface with real-time progress tracking.
- The file manager provides cPanel-like directory browsing with breadcrumb navigation and level-aware display.

**Technical Implementations & Feature Specifications:**
- **Donation Management:** Supports PayPal and Coinbase Commerce integrations.
- **Cache File Distribution:**
    - Features a high-performance chunked upload system utilizing the TUS protocol, splitting files into 5MB chunks for parallel and resumable uploads.
    - Asynchronous processing handles hash computation and manifest regeneration in background jobs.
    - Implements a smart hashing strategy, using fast MD5 for new files and SHA256 only for duplicate detection, significantly improving upload speeds.
    - Includes robust security features: directory traversal protection, filename sanitization, path normalization, null byte filtering, and storage isolation.
    - The file manager supports full directory browsing, virtual folders, and path-based filtering.
    - **Critical Path Fix (Oct 13, 2025):** Corrected `relative_path` storage throughout the system to store ONLY directory paths (excluding filenames), preventing files from being misidentified as folders.
    - **Extract Here Feature:** Preserves complete directory structure when extracting archives - files are stored in `cache_files/{directory_path}/{filename}` maintaining folder hierarchy.
    - **Duplicate Detection:** Normalized to use directory paths only for accurate duplicate checking across all upload methods.
    - **Drag & Drop:** Enhanced with folder detection to gracefully handle browser restrictions, directing users to "Browse Folders" button.
    - **Cleanup Command:** `php artisan cache:fix-paths` safely repairs existing database records with incorrect paths using metadata validation.
- **Multi-site Voting System:** Tracks votes and rewards.
- **Client Management:** Facilitates the distribution and management of game client versions.

**System Design Choices:**
- **Database:** SQLite is used for development and portability within the Replit environment, with a recommendation to switch to MySQL/PostgreSQL for production.
- **Performance Optimization:** Critical PHP upload limits (`upload_max_filesize`, `post_max_size`, `memory_limit`, `max_execution_time`, `max_input_time`) are configured for handling large files.
- **Queue System:** Laravel queues are essential for asynchronous processing of chunked uploads and file manipulations.
- **API Endpoints:** A structured API provides endpoints for donations, cache management (manifest, download, stats), and voting.

### External Dependencies
- **Payment Gateways:**
    - PayPal
    - Coinbase Commerce
- **File Upload Protocol:**
    - TUS Protocol (via Uppy.js and a custom PHP library)
- **Frontend Libraries:**
    - Uppy.js (for chunked upload UI)
- **Database:**
    - SQLite (development)
    - MySQL / PostgreSQL (recommended for production)