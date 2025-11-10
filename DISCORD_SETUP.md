# Discord Webhook Setup Guide

This guide will help you configure Discord webhooks to send update notifications from your admin panel.

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

## Step 3: Using the Feature

1. Go to your admin panel at `/admin/updates`
2. Find the update you want to send to Discord
3. Click the Discord button (purple button with Discord icon)
4. Confirm the action
5. The update will be posted to your Discord channel

## Features

The Discord message will include:
- **Title**: The update title (clickable link to full update)
- **Description**: Formatted content from your update
- **Color**: Dragon red theme (#c41e3a)
- **Timestamp**: When the update was published
- **Thumbnail**: Featured image (if set)
- **Author**: Author name (if set)
- **Fields**: Category, update type, and featured status

## Content Formatting

The system automatically converts your update blocks to Discord-friendly markdown:

- **Headers**: Converted to bold text with # prefixes
- **Paragraphs**: Plain text
- **Lists**: Bullet points (•) or numbered lists
- **Code blocks**: Formatted with triple backticks
- **Alerts**: Displayed with emoji icons (ℹ️, ⚠️, ✅, ❌)
- **Callouts**: Formatted with title and emoji (💡, ⚠️, ❗, ✨)
- **OSRS Headers**: Bold quote-style formatting
- **Patch Notes**: Section with 🔧 icon
- **Custom Sections**: Your custom tag + title with nested content
- **Separators**: Decorative line separators

## Limitations

- Discord has a 4096 character limit for embed descriptions
- Very long updates will be truncated with "..." at the end
- The full update is always available via the clickable title link
- Images in content blocks are not included (only the featured image)
- Tables are not supported in Discord embeds

## Troubleshooting

**Error: "Discord webhook URL is not configured"**
- Make sure you've added `DISCORD_WEBHOOK_URL` to your `.env` file
- Restart your server after updating `.env`

**Error: "Failed to send update to Discord"**
- Verify your webhook URL is correct
- Check that the webhook hasn't been deleted in Discord
- Ensure your server has internet access

**Update sent but not appearing in Discord**
- Check that the webhook is pointing to the correct channel
- Verify the channel exists and the webhook has permissions
- Check Discord's server status

## Testing

To test your webhook configuration:
1. Create a simple test update in the admin panel
2. Click the Discord button
3. Check your Discord channel for the message
4. If successful, you can delete the test update

## Security Notes

- Never commit your `.env` file to version control
- Keep your webhook URL private - anyone with it can post to your channel
- Consider creating a dedicated channel for update notifications
- You can regenerate the webhook URL in Discord if it's compromised
