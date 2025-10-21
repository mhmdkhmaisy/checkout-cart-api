# PHP GD Extension - Installation Guide

## What Happened?

The original banner generator used PHP's GD library for image manipulation, but your XAMPP installation doesn't have it enabled.

## Solution 1: Use SVG Version (Current - No Setup Required!)

✅ **I've updated the BannerController to use SVG instead of GD.**

**Benefits:**
- Works on any PHP installation (no extensions needed)
- Scalable vector graphics (looks sharp at any size)
- Smaller file size
- Supports animations natively
- No setup required!

The current implementation now generates SVG banners with:
- Pixelated yellow text
- Pink animated edges
- Same visual style as before

## Solution 2: Enable GD Extension (Optional - For PNG Images)

If you prefer PNG images over SVG, you can enable the GD extension in XAMPP:

### Windows (XAMPP)

1. **Open `php.ini` file:**
   - XAMPP Control Panel → Click "Config" next to Apache → Select "PHP (php.ini)"
   - Or manually open: `C:\xampp\php\php.ini`

2. **Find and uncomment this line:**
   ```ini
   ;extension=gd
   ```
   
   Remove the semicolon to make it:
   ```ini
   extension=gd
   ```

3. **Save the file and restart Apache** in XAMPP Control Panel

4. **Verify GD is enabled:**
   ```bash
   php -m | findstr gd
   ```

### Linux/Mac

GD is usually enabled by default. If not:

```bash
# Ubuntu/Debian
sudo apt-get install php-gd
sudo systemctl restart apache2

# Mac (Homebrew)
brew install php
brew services restart php
```

## Current Status

✅ **SVG banners are working** - No action needed!

The banners will now be generated as SVG images at:
- `/banner/generate?text=STORE&width=200&height=80`
- Returns `image/svg+xml` instead of `image/png`

**All features remain:**
- Pixelated text
- Pink dragon edges
- Animated glow
- Custom text and dimensions
