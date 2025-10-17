# Aragon RSPS - Original Color Scheme Backup

**Date:** October 17, 2025

## Current Public Layout Colors (public.blade.php)

```css
:root {
    --primary-color: #d40000;
    --primary-bright: #ff0000;
    --secondary-color: #1a1a1a;
    --accent-color: #0a0a0a;
    --text-light: #e8e8e8;
    --text-dark: #333333;
    --text-muted: #999999;
    --text-gold: #d4af37;
    --background-dark: #0a0a0a;
    --background-gradient: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
    --card-background: rgba(26, 26, 26, 0.95);
    --border-color: #333333;
    --border-gold: rgba(212, 175, 55, 0.3);
    --hover-color: rgba(212, 0, 0, 0.15);
}

body {
    background: #000000;
    background-image: 
        radial-gradient(circle at 20% 50%, rgba(212, 0, 0, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(212, 0, 0, 0.05) 0%, transparent 50%);
}
```

## Aragon Branding Colors (from codebase)

```css
'dragon-black': #0a0a0a
'dragon-surface': #1a1a1a
'dragon-red': #d40000
'dragon-red-bright': #ff0000
'dragon-silver': #e8e8e8
'dragon-silver-dark': #c0c0c0
'dragon-border': #333333
```

## Current Alert Colors

- Success: #22c55e (green)
- Error: #ef4444 (red)
- Warning: #f59e0b (amber)
- Info: #3b82f6 (blue)

## Files Using These Colors

1. resources/views/layouts/public.blade.php
2. resources/views/play.blade.php
3. resources/views/vote/index.blade.php
4. resources/views/store/index.blade.php

---

**Restore Instructions:**
If you need to revert to the original colors, use these values in the public.blade.php file.
