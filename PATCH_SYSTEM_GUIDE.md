# Efficient Patch Delta Management System

## Overview

This system implements an efficient patch delta management approach optimized for shared hosting environments with limited bandwidth and I/O capacity.

## Key Features

### 1. **Incremental ZIP Updates**
Instead of recreating the entire combined patch from scratch each time, the system:
- Opens the existing combined ZIP file
- Adds only the new/modified files from the latest delta
- Updates the central directory
- Deletes the old combined file

**Advantages:**
- Only writes 5MB for a 5MB delta (instead of 200MB+)
- Minimal disk I/O
- Works efficiently on shared hosting

### 2. **Static File Serving**
All patches are stored as static files in `public/patches/`:
```
public/patches/
├── base_1.0.0.zip                    # Initial base patch
├── delta_1.0.0_1.0.1.zip             # Delta from 1.0.0 to 1.0.1
├── delta_1.0.1_1.0.2.zip             # Delta from 1.0.1 to 1.0.2
├── combined_0.0.0_1.0.2.zip          # Combined patch for new users
└── manifest.json                      # Patch manifest
```

### 3. **Smart Download Strategy**

| User State               | Download Strategy                          |
| ------------------------ | ------------------------------------------ |
| New user (0.0.0)         | Download `combined_0.0.0_latest.zip`       |
| Partial user (has 1.0.1) | Download only `delta_1.0.1_1.0.2.zip`      |
| Fully updated            | No download                                |

### 4. **Manifest System**
The system maintains a `manifest.json` file that provides a single source of truth:

```json
{
  "latest_version": "1.0.2",
  "generated_at": "2025-10-29T12:34:56Z",
  "patches": [
    {
      "from": "0.0.0",
      "to": "1.0.0",
      "file": "base_1.0.0.zip",
      "size": 209715200,
      "file_count": 1500,
      "is_base": true,
      "created_at": "2025-10-29T10:00:00Z"
    },
    {
      "from": "1.0.0",
      "to": "1.0.1",
      "file": "delta_1.0.0_1.0.1.zip",
      "size": 5242880,
      "file_count": 25,
      "is_base": false,
      "created_at": "2025-10-29T11:00:00Z"
    },
    {
      "from": "0.0.0",
      "to": "1.0.2",
      "file": "combined_0.0.0_1.0.2.zip",
      "size": 215000000,
      "is_combined": true,
      "created_at": "2025-10-29T12:00:00Z"
    }
  ]
}
```

## API Endpoints

### Get Patch Manifest
```
GET /admin/cache/patches/manifest
```
Returns the complete patch manifest with all available patches.

### Get Latest Version
```
GET /admin/cache/patches/latest
```
Returns the latest version and basic patch info.

### Check for Updates
```
POST /admin/cache/patches/check-updates
Body: { "current_version": "1.0.1" }
```
Returns available updates for the client's version.

### Download Combined Patches
```
POST /admin/cache/patches/download-combined
Body: { "from_version": "0.0.0" }
```
Downloads the appropriate combined patch based on the client's version.

## How It Works

### Patch Creation Flow

1. **File Upload & Extraction**
   - User uploads a ZIP file via chunked upload
   - Files are extracted and processed
   - System detects changes compared to the previous version

2. **Delta Patch Generation**
   - Only changed/new files are added to a delta ZIP
   - Delta is stored as `delta_X.X.X_Y.Y.Y.zip`
   - Database is updated with patch metadata

3. **Combined Patch Update (Incremental)**
   ```php
   // Pseudo-code
   1. Copy current combined_0.0.0_X.X.X.zip to combined_0.0.0_Y.Y.Y.zip
   2. Open both combined and delta ZIPs
   3. For each file in delta:
      - Delete old version from combined (if exists)
      - Add new version to combined
   4. Close both ZIPs
   5. Delete old combined file
   ```

4. **Manifest Update**
   - `manifest.json` is regenerated with all patches
   - Includes file sizes, counts, and creation dates

### Download Flow

**For New Users (version 0.0.0):**
1. Client requests patches from version `0.0.0`
2. Server serves pre-built `combined_0.0.0_latest.zip`
3. Client downloads single file with all content
4. Total bandwidth: ~200MB

**For Existing Users (version 1.0.1):**
1. Client requests patches from version `1.0.1`
2. Server identifies required delta: `delta_1.0.1_1.0.2.zip`
3. Client downloads only the delta
4. Total bandwidth: ~5MB

**For Users with Intermediate Versions:**
1. Client requests patches from version `1.0.0`
2. Server builds combined patch on-the-fly from deltas
3. Client downloads combined patch
4. Result is cached for future requests

## Storage Optimization

The system minimizes storage by:
- Keeping only one combined patch (latest)
- Storing individual deltas separately
- Deleting old combined patches when new ones are created
- Total storage: ~1.2x the size of latest full cache (not exponential)

## Performance Benefits

### Bandwidth Savings
- New users: No change (still download full cache)
- Existing users: Only download changed files (95%+ reduction)
- Shared hosting friendly: No dynamic ZIP building for common case

### I/O Savings
- Incremental updates: Only write 5MB instead of 200MB+
- No extraction/re-compression of unchanged files
- Fast operation even on slow shared hosting disks (6 MB/s)

### CPU Savings
- Pre-built combined patches for new users
- Minimal ZIP operations per patch creation
- No need for X-Sendfile or nginx modules

## Migration Notes

### From Old System
The old system is still supported for backward compatibility:
- Legacy `combinePatchesForDownload()` still works
- Old patch paths are still valid
- Gradual migration as new patches are created

### First Run After Implementation
When the first new patch is created after implementation:
1. Delta patch will be created normally
2. Combined patch will be generated (one-time cost)
3. Subsequent patches will use incremental update
4. Manifest will be generated

## Best Practices

1. **Regular Cleanup**
   - Old delta patches can be deleted after major version merges
   - Keep at least the last 3-5 deltas for granular updates

2. **Monitoring**
   - Watch combined patch file sizes
   - Monitor bandwidth usage per client type
   - Track delta sizes to identify large updates

3. **Version Strategy**
   - Use semantic versioning (X.Y.Z)
   - Increment patch number (Z) for small updates
   - Increment minor (Y) for medium updates, merge deltas
   - Increment major (X) when merging into new base

## Troubleshooting

### Combined Patch Missing
If `combined_0.0.0_latest.zip` doesn't exist:
- System will fall back to building it on-the-fly
- Subsequent requests will use the built version
- Trigger manual rebuild via patch merge operation

### Manifest Out of Date
Manifest is automatically updated on:
- New patch creation
- Patch deletion
- Patch merge operation

Manual update:
```php
$patchService = new CachePatchService();
$patchService->updatePatchManifestPublic();
```

### Large Delta Sizes
If deltas are getting too large:
- Consider creating a new base patch via merge operation
- Check if unnecessary files are being included
- Verify change detection is working correctly

## Code References

### Key Files
- `app/Services/CachePatchService.php` - Core patch logic
- `app/Http/Controllers/Admin/CacheFileController.php` - API endpoints
- `app/Models/CachePatch.php` - Patch model

### Key Methods
- `generatePatchFromDatabase()` - Creates new patches
- `updateCombinedPatch()` - Incremental ZIP update
- `updatePatchManifest()` - Regenerates manifest
- `combinePatchesForDownload()` - Smart download selection
