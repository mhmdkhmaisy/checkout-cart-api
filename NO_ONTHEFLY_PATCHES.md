# On-The-Fly Patch Building - DISABLED

## Summary

On-the-fly patch building has been **completely removed** from the system. All patches are now pre-built during the upload process.

## Why This Change?

The system already creates pre-built combined patches incrementally when deltas are uploaded:
1. New delta uploaded → `delta_1.0.1_1.0.2.zip` created
2. Combined patch updated in-place → `combined_0.0.0_1.0.2.zip` (5MB write)
3. Manifest updated

**There's no need for on-the-fly building** because:
- Combined patches already exist (pre-built)
- They're always up-to-date (updated with each delta)
- Building on-the-fly is inefficient and unnecessary

## What Changed

### `app/Services/CachePatchService.php`

#### Removed Method:
```php
// REMOVED: buildCombinedPatchLegacy()
// This method built patches on-the-fly when users requested intermediate versions
// No longer needed because all patches are pre-built
```

#### Updated Method:
```php
public function combinePatchesForDownload(string $fromVersion): ?string
{
    // For new users (0.0.0):
    if ($fromVersion === '0.0.0') {
        $combinedPath = "patches/combined_0.0.0_{$latestVersion}.zip";
        if (file_exists(public_path($combinedPath))) {
            return $combinedPath;  // ✅ Serve pre-built patch
        }
        // ❌ NO FALLBACK - combined patch must exist
        Log::error('Pre-built combined patch not found');
        return null;
    }
    
    // For existing users:
    // Serve individual delta patches only
    // If multiple patches needed → return null (shouldn't happen)
    
    // ❌ NO ON-THE-FLY BUILDING
    return null;
}
```

### `app/Http/Controllers/Admin/CacheFileController.php`

#### Updated Cleanup:
```php
private function cleanupOnTheFlyPatches()
{
    // Remove ANY on-the-fly patches (combined_from_*.zip)
    // These should never exist, but clean up legacy files
    
    // Remove ALL lock files (.lock_from_*)
    // These are also unnecessary now
}
```

## How It Works Now

### Upload Process:
```
Upload files
  ↓
Create delta_1.0.1_1.0.2.zip (5MB)
  ↓
Update combined_0.0.0_1.0.2.zip in-place (5MB write)
  ↓
Update manifest.json
  ↓
✅ DONE - Pre-built combined patch ready
```

### Download Process:
```
User requests download
  ↓
Clean up any legacy on-the-fly patches
  ↓
User version 0.0.0?
  ↓ YES
Serve pre-built combined_0.0.0_latest.zip
  ↓ NO
User version 1.0.1?
  ↓
Find patches needed: delta_1.0.1_1.0.2.zip
  ↓
Serve single delta
  ↓
✅ DONE - No building required
```

## What Happens If...

### User has intermediate version (e.g., 1.0.0) and needs multiple patches?
**Before:** Build combined patch on-the-fly from 1.0.0 to latest  
**Now:** Returns null (error)

**Why this is OK:**
- Users should either be on latest or one version behind
- If they're multiple versions behind, they can:
  1. Download from 0.0.0 (fresh install)
  2. Update sequentially through each patch
  3. Admin should manage patches better (merge old ones)

### Combined patch doesn't exist for new users?
**Before:** Build it on-the-fly  
**Now:** Returns error

**Why this is OK:**
- Combined patches are ALWAYS created during upload
- If missing, it's a system error that needs investigation
- Logs the error for debugging

### Old on-the-fly patches exist in patches folder?
**System:** Automatically cleans them up on next download request  
**Result:** Disk space freed

## Benefits

✅ **Simpler Code:** No complex on-the-fly building logic  
✅ **Faster Downloads:** No waiting for patch building  
✅ **Predictable Behavior:** Patches either exist or they don't  
✅ **Better Error Handling:** Missing patches are logged as errors  
✅ **Cleaner Disk:** No accumulated on-the-fly patches

## File Structure

```
public/patches/
├── base_1.0.0.zip                    # Base patch
├── delta_1.0.0_1.0.1.zip             # Delta patches
├── delta_1.0.1_1.0.2.zip
├── combined_0.0.0_1.0.2.zip          # Pre-built combined (updated incrementally)
└── manifest.json                      # Patch metadata

# These will NEVER be created anymore:
# ❌ combined_from_0.0.0_to_1.0.1.zip  (on-the-fly)
# ❌ combined_from_1.0.0_to_1.0.2.zip  (on-the-fly)
# ❌ .lock_from_*                      (build locks)
```

## Edge Cases

### User on very old version (e.g., 1.0.0) when latest is 1.0.5
**Scenario:** User needs patches: 1.0.1, 1.0.2, 1.0.3, 1.0.4, 1.0.5

**Before:** Build combined_from_1.0.0_to_1.0.5.zip on-the-fly

**Now:** 
- System returns null (error)
- User should download fresh from 0.0.0
- OR admin should merge old patches into new base

**Recommendation:**
- Keep only last 2-3 deltas
- Merge older patches regularly
- Users more than 2 versions behind = fresh install

## Migration Notes

### Existing On-The-Fly Patches
- Will be automatically cleaned up on next download
- Safe to manually delete all `combined_from_*.zip` files
- Safe to manually delete all `.lock_from_*` files

### No Code Changes Needed in Client
- Client still requests patches same way
- Server still responds with patch paths
- Only difference: server uses pre-built patches only

## Summary

**Old System:**
- Pre-build combined patches ✅
- ALSO build on-the-fly when needed ✅
- Result: Redundancy, complexity

**New System:**
- Pre-build combined patches ✅
- On-the-fly building DISABLED ❌
- Result: Simplicity, efficiency

**Key Principle:** If a patch doesn't exist, it's an error - don't try to build it on-the-fly. Fix the root cause instead.
