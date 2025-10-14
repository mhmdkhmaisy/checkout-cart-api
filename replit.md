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
- The file manager provides cPanel-like directory browsing with breadcrumb navigation and level-aware display.
- Supports drag-and-drop file uploads, folder uploads, and archive extraction with preserved directory structure.

**Technical Implementations & Feature Specifications:**
- **Donation Management:** Supports PayPal and Coinbase Commerce integrations.
- **Cache File Distribution:**
    - Features a standard multi-file upload system with support for individual files, folders, and archive extraction (ZIP/TAR).
    - Implements a smart hashing strategy, using SHA256 for file identification and duplicate detection.
    - Includes robust security features: directory traversal protection, filename sanitization, path normalization, null byte filtering, and storage isolation.
    - The file manager supports full directory browsing, virtual folders, and path-based filtering.
    - **Critical Path Fix (Oct 13, 2025):** Corrected `relative_path` storage throughout the system to store ONLY directory paths (excluding filenames), preventing files from being misidentified as folders.
    - **Extract Here Feature:** Preserves complete directory structure when extracting archives - files are stored in `cache_files/{directory_path}/{filename}` maintaining folder hierarchy.
    - **Duplicate Detection:** Normalized to use directory paths only for accurate duplicate checking across all upload methods.
    - **Drag & Drop:** Enhanced with folder detection to gracefully handle browser restrictions, directing users to "Browse Folders" button.
    - **Cleanup Command:** `php artisan cache:fix-paths` safely repairs existing database records with incorrect paths using metadata validation.
    - **Delta Patch System (Oct 14, 2025):** Implemented intelligent incremental patch system for efficient cache updates:
        - **Semantic Versioning:** Automatic version management (1.0.0, 1.0.1, etc.) with proper semantic version comparison
        - **Incremental Patches:** Only changed/new files are packaged in delta patches, drastically reducing download sizes
        - **Base Patches:** Full cache snapshots serve as foundation for delta chains
        - **Auto-Merge:** Automatically consolidates 15+ incremental patches into new base version for optimal performance
        - **Manifest System:** Dual manifest storage - JSON files for full state tracking, database records for delta diffs
        - **Smart Diffing:** MD5 hashing compares against previous full state to accurately detect changes
        - **API Endpoints:** Client can check for updates, download individual patches, or get combined patch bundles
        - **Artifact Exclusion:** scanDir() explicitly filters out patch system directories to prevent corruption
        - **Storage Paths:** Patches stored in `cache/patches/`, manifests in `cache/manifests/`, combined downloads in `cache/combined/`
    - **Optimized Storage Architecture (Oct 14, 2025):** Refactored to eliminate duplicate storage and optimize disk usage:
        - **Temporary Upload Processing:** Files are uploaded to `temp_uploads/` directory for processing only
        - **Database as Authority:** Database maintains authoritative file records (metadata, hashes, paths) - not filesystem
        - **Patch-Only Storage:** Only compressed patch ZIPs are permanently stored, original uploads are deleted after patch creation
        - **Smart Cleanup:** Temporary files automatically deleted after patch generation, database records preserved
        - **Incremental Detection:** Patch system compares database state against previous manifest to identify only new/changed files
        - **Example Flow:** Upload base → DB records → Patch v1.0.0 created → Temp cleanup → Upload sprites → DB updated → Compare vs v1.0.0 → Patch v1.0.1 (sprites only) → Cleanup
        - **Zero Duplication:** Eliminates previous issue where files were stored both as originals in `cache_files/` AND in patch ZIPs
    - **Batch Upload Fixes (Oct 14, 2025):** Resolved critical issues with "Browse Directory" uploads:
        - **Single Patch Per Upload:** Fixed multiple patch generation by deferring manifest/patch creation until all batches complete via new `finalizeUpload()` endpoint
        - **Missing Files Fix:** Reduced batch sizes from 50/20 to 15/15 files to respect PHP `max_file_uploads=20` limit, preventing silent file drops
        - **Batch Flow:** Individual batch uploads skip manifest → All batches complete → Single finalize call → One patch created
        - **PHP Limit Awareness:** Batch sizes now safely stay below default PHP upload limits with margin for error
    - **Patch Analysis & Insights (Oct 14, 2025):** Advanced patch management tools for monitoring and debugging:
        - **Patch Comparison:** Side-by-side diff viewer shows added, removed, and modified files between any two patch versions with color-coded visual indicators
        - **Changelog Generation:** Automatically creates human-readable changelogs from patch metadata, listing all file changes with timestamps
        - **File History Tracking:** Traces individual file modifications across all patches with interactive directory tree navigation
        - **Integrity Verification:** Validates patch checksums against database records to detect corruption or tampering
        - **View Data Modal:** Interactive collapsible directory tree showing patch contents with file paths and hashes
        - **Security Note:** Admin routes currently lack authentication middleware (intentional for development) - must add auth before production
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
- **Database:**
    - SQLite (development)
    - MySQL / PostgreSQL (recommended for production)