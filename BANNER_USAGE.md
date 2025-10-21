# Aragon RSPS Banner Generator

## Overview
Dynamic SVG banner generator with pixelated yellow text and animated pink dragon edges, matching the Aragon logo style.

**✅ No PHP Extensions Required** - Uses SVG instead of GD library, works on any PHP installation!

## Features
- **Pixelated retro-style text** - Custom 5x5 pixel font renderer
- **Animated pink edges** - Gradient borders with pulsing glow effect
- **Customizable dimensions** - Default 200x80px, fully adjustable
- **Yellow text with shadow** - High contrast gold (#FFD700) text
- **CSS animations** - Smooth pulsing edge effects
- **SVG format** - Scalable, crisp at any size, no PHP extensions needed

## Usage

### 1. Blade Component (Recommended)
```blade
<x-animated-banner text="STORE" :width="200" :height="80" />
```

### 2. Direct Image URL
```html
<img src="{{ route('banner.generate', ['text' => 'VOTE NOW', 'width' => 200, 'height' => 80]) }}" alt="Banner">
```

### 3. Plain URL
```
/banner/generate?text=PROMOTION&width=200&height=80
```

## Examples

### Store Page Banner
```blade
<x-animated-banner text="STORE" />
```

### Vote Page Banner
```blade
<x-animated-banner text="VOTE NOW" :width="220" />
```

### Home Page Banner
```blade
<x-animated-banner text="PLAY NOW" />
```

### Promotion Banner
```blade
<x-animated-banner text="50% OFF" :width="180" />
```

## Parameters

| Parameter | Default | Description |
|-----------|---------|-------------|
| `text` | "PROMOTION" | Banner text (uppercase recommended, max 20 chars) |
| `width` | 200 | Image width in pixels (100-500) |
| `height` | 80 | Image height in pixels (50-200) |

## Supported Characters
- Letters: A-Z
- Numbers: 0-9
- Special: ! ? % + - (space)

## Technical Details

### Color Palette
- **Background**: #0A0A0A (dark black)
- **Text**: #FFD700 (gold yellow)
- **Text Shadow**: #645000 (dark gold)
- **Edge Gradient**: Pink to red (#FF6496 to #C83264)

### Animation
- **Pulse Effect**: 2s ease-in-out infinite
- **Glow Intensity**: 0.4 to 0.6 opacity

## Demo Page
Visit `/banner/demo` to see live examples and generate custom banners.

## Controller Location
`app/Http/Controllers/BannerController.php`

## Component Location
`resources/views/components/animated-banner.blade.php`

## Route
```php
Route::get('/banner/generate', [BannerController::class, 'generate'])->name('banner.generate');
```
