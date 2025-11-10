# Discord Webhook Setup Guide (Screenshot Version)

This guide will help you configure Discord webhooks to send update notifications as **screenshot images** from your admin panel, making your Discord posts look exactly like your website!

## How It Works

When you click the Discord button:
1. A popup window opens showing your update in screenshot-ready format
2. JavaScript captures a high-quality screenshot of the rendered page
3. The screenshot is automatically uploaded and sent to Discord
4. Discord displays your update as an image that looks exactly like your website
5. A link is included for users to read the full update

## Step 1: Create a Discord Webhook

1. Open your Discord server
2. Go to Server Settings → Integrations
3. Click "Webhooks" or "Create Webhook"
4. Click "New Webhook"
5. Give it a name (e.g., "Update Notifications")
6. Select the channel where updates should be posted
7. (Optional) Upload an avatar for the webhook
8. Click "Copy Webhook URL"

## Step 2: Configure Your Application

1. Open your `.env` file
2. Add the following line with your webhook URL:
   ```
   DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/YOUR_WEBHOOK_ID/YOUR_WEBHOOK_TOKEN
   ```
3. Replace the URL with the one you copied from Discord
4. Save the file

### For Shared Hosting Users:
After updating `.env`, run these commands in your app root directory:
```bash
php artisan config:clear
php artisan config:cache
```

No server restart needed on shared hosting!

## Step 3: Using the Feature

1. Go to your admin panel at `/admin/updates`
2. Find the update you want to send to Discord
3. Click the Discord button (purple button with Discord icon)
4. Confirm the action
5. A popup window will appear capturing the screenshot
6. The window will close automatically when complete
7. Check your Discord channel - your update appears as a beautiful image!

## What Gets Sent to Discord

### Message Format:
- **Image**: High-quality screenshot of your rendered update (title, content blocks, featured image, etc.)
- **Text**: "📢 New Update: [Title]"
- **Link**: Direct link to read the full update on your website

### Screenshot Includes:
- Update title styled with your theme
- Author and date information
- Featured image (if set)
- All content blocks rendered exactly as they appear on your site:
  - Headers, paragraphs, lists
  - Code blocks
  - Alerts and callouts
  - OSRS headers
  - Patch notes sections
  - Custom sections (with your dynamic tags!)
  - Separators
- "Read full update" link at the bottom

## Technical Details

### Browser Requirements:
- Works in all modern browsers (Chrome, Firefox, Safari, Edge)
- Requires JavaScript enabled
- Uses HTML2Canvas library for screenshot capture
- No server dependencies - works perfectly on shared hosting!

### Image Quality:
- 2x resolution for crisp, high-quality images
- PNG format for best quality
- Maximum file size: 10MB (more than enough)
- Background color matches your dark theme

### Performance:
- Screenshot capture takes 1-3 seconds
- Automatic upload and posting to Discord
- No manual steps required

## Advantages Over Text-Based Format

✅ **Visual Impact**: Your updates look exactly like your website  
✅ **Brand Consistency**: Maintains your custom styling and colors  
✅ **Better Engagement**: Images get more attention than text  
✅ **Custom Sections**: All your dynamic custom tags display perfectly  
✅ **Professional**: Polished appearance in Discord  
✅ **No Formatting Loss**: Every design element preserved  

## Troubleshooting

**Error: "Discord webhook URL is not configured"**
- Make sure you've added `DISCORD_WEBHOOK_URL` to your `.env` file
- On shared hosting, run `php artisan config:clear && php artisan config:cache`
- Verify the URL format is correct

**Popup Blocked**
- Your browser may block the screenshot capture popup
- Allow popups for your admin domain
- Try clicking the Discord button again

**Screenshot Looks Wrong**
- Ensure your update page loads correctly first
- Check that featured images are accessible (not blocked by CORS)
- Verify your CSS is loading properly

**Discord Shows "Failed to send"**
- Verify your webhook URL is correct and active
- Check that the webhook hasn't been deleted in Discord
- Ensure your server has internet access
- Check file size is under 10MB

**Popup Doesn't Close Automatically**
- Check browser console for JavaScript errors
- Verify the upload route is accessible
- Wait up to 30 seconds (automatic timeout)

## Testing

To test your webhook configuration:
1. Create a simple test update in the admin panel
2. Click the Discord button
3. The popup should open, capture, and close automatically
4. Check your Discord channel for the screenshot
5. Click the link to verify it goes to your update page
6. Delete the test update if desired

## Security Notes

- Never commit your `.env` file to version control
- Keep your webhook URL private - anyone with it can post to your channel
- Consider creating a dedicated channel for update notifications
- You can regenerate the webhook URL in Discord if it's compromised
- Screenshots are temporary and not stored on your server

## Browser Compatibility

✅ Chrome/Edge (Chromium): Excellent  
✅ Firefox: Excellent  
✅ Safari: Good (may have minor rendering differences)  
⚠️ Internet Explorer: Not supported (use a modern browser)

## Customization

### Adjusting Screenshot Width

Edit `resources/views/updates/screenshot.blade.php`:
```html
<style>
    body {
        width: 1200px; /* Change this value */
    }
</style>
```

### Changing Screenshot Quality

Edit the same file:
```javascript
html2canvas(captureArea, {
    scale: 2, // Change to 1 for lower quality, 3 for higher
    // ...
})
```

### Custom Message Format

Edit `app/Http/Controllers/Admin/UpdateController.php` in the `processScreenshot` method:
```php
'content' => "📢 **New Update: {$update->title}**\n\n🔗 Read more: {$updateUrl}"
```

## Reverting to Text-Based Format

If you prefer the old text-based format instead of screenshots, check the git history for the previous version of the `sendToDiscord` method that uses embeds with formatted text.

## Support

For issues with:
- **Discord webhooks**: Check Discord's webhook documentation
- **Screenshot capture**: Ensure JavaScript is enabled and popups are allowed
- **Styling issues**: Verify your CSS files are loading correctly

---

**Enjoy sending beautiful, website-like updates to your Discord community! 🎨**
