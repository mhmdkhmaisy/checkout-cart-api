<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Update;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Helpers\UpdateRenderer;

class UpdateController extends Controller
{
    public function index(Request $request)
    {
        $query = Update::query();
        
        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'published') {
                $query->where('is_published', true);
            } elseif ($status === 'draft') {
                $query->where('is_published', false);
            } elseif ($status === 'featured') {
                $query->where('is_featured', true);
            } elseif ($status === 'pinned') {
                $query->where('is_pinned', true);
            }
        }
        
        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }
        
        // Sort by pinned first, then created_at
        $updates = $query->with(['attachedToUpdate'])
                         ->orderBy('is_pinned', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->paginate(20);
        
        // Get statistics
        $stats = [
            'total' => Update::count(),
            'published' => Update::where('is_published', true)->count(),
            'draft' => Update::where('is_published', false)->count(),
            'featured' => Update::where('is_featured', true)->count(),
            'pinned' => Update::where('is_pinned', true)->count(),
        ];
        
        // Get categories for filter
        $categories = Update::whereNotNull('category')
                            ->distinct()
                            ->pluck('category')
                            ->filter();
        
        return view('admin.updates.index', compact('updates', 'stats', 'categories'));
    }

    public function create()
    {
        return view('admin.updates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'author' => 'nullable|string|max:100',
            'meta_description' => 'nullable|string|max:160',
            'client_update' => 'boolean',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'is_pinned' => 'boolean',
            'published_at' => 'nullable|date',
            'attached_to_update_id' => 'nullable|exists:updates,id',
        ]);

        $validated['client_update'] = $request->has('client_update');
        $validated['is_published'] = $request->has('is_published');
        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_pinned'] = $request->has('is_pinned');

        $update = Update::create($validated);

        return redirect()->route('admin.updates.index')
            ->with('success', 'Update created successfully.');
    }

    public function edit(Update $update)
    {
        return view('admin.updates.edit', compact('update'));
    }

    public function update(Request $request, Update $update)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'author' => 'nullable|string|max:100',
            'meta_description' => 'nullable|string|max:160',
            'client_update' => 'boolean',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'is_pinned' => 'boolean',
            'published_at' => 'nullable|date',
            'attached_to_update_id' => 'nullable|exists:updates,id',
        ]);

        $validated['client_update'] = $request->has('client_update');
        $validated['is_published'] = $request->has('is_published');
        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_pinned'] = $request->has('is_pinned');

        $update->update($validated);

        return redirect()->route('admin.updates.index')
            ->with('success', 'Update updated successfully.');
    }

    public function destroy(Update $update)
    {
        $update->delete();

        return redirect()->route('admin.updates.index')
            ->with('success', 'Update deleted successfully.');
    }
    
    public function togglePublish(Update $update)
    {
        $update->is_published = !$update->is_published;
        if ($update->is_published && empty($update->published_at)) {
            $update->published_at = now();
        }
        $update->save();
        
        $status = $update->is_published ? 'published' : 'unpublished';
        return redirect()->back()->with('success', "Update {$status} successfully.");
    }
    
    public function toggleFeatured(Update $update)
    {
        $update->is_featured = !$update->is_featured;
        $update->save();
        
        $status = $update->is_featured ? 'marked as featured' : 'unmarked as featured';
        return redirect()->back()->with('success', "Update {$status} successfully.");
    }
    
    public function togglePinned(Update $update)
    {
        $update->is_pinned = !$update->is_pinned;
        $update->save();
        
        $status = $update->is_pinned ? 'pinned' : 'unpinned';
        return redirect()->back()->with('success', "Update {$status} successfully.");
    }
    
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120'
        ]);
        
        $file = $request->file('image');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        $uploadsPath = public_path('assets/updates');
        if (!file_exists($uploadsPath)) {
            mkdir($uploadsPath, 0755, true);
        }
        
        $file->move($uploadsPath, $filename);
        
        $url = '/assets/updates/' . $filename;
        
        return response()->json([
            'success' => true,
            'url' => $url
        ]);
    }

    public function sendToDiscord(Update $update)
    {
        $webhookUrl = config('services.discord.webhook_url');
        
        if (!$webhookUrl) {
            return redirect()->back()->with('error', 'Discord webhook URL is not configured. Please add DISCORD_WEBHOOK_URL to your .env file.');
        }

        try {
            // Convert update content to Discord format with proper image positioning
            $content = json_decode($update->content, true);
            $updateUrl = route('updates.show', $update->slug);
            
            // Build header message
            $headerMessage = "📢 **New Update: {$update->title}**\n";
            
            if ($update->category) {
                $headerMessage .= "**Category:** {$update->category}\n";
            }
            
            if ($update->author) {
                $headerMessage .= "**Author:** {$update->author}\n";
            }
            
            // Send header
            $response = Http::post($webhookUrl, ['content' => $headerMessage]);
            if (!$response->successful()) {
                throw new \Exception('Discord API error: ' . $response->body());
            }
            usleep(300000); // 0.3 second delay
            
            // Process content blocks and send with images in proper positions
            $this->sendContentBlocksToDiscord($content, $webhookUrl);
            
            // Send footer with link
            $footerMessage = "🔗 **Read full update:** {$updateUrl}";
            $response = Http::post($webhookUrl, ['content' => $footerMessage]);
            if (!$response->successful()) {
                throw new \Exception('Discord API error: ' . $response->body());
            }
            
            return redirect()->back()->with('success', 'Update sent to Discord successfully!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send to Discord: ' . $e->getMessage());
        }
    }

    private function sendContentBlocksToDiscord($content, $webhookUrl, &$textBuffer = '')
    {
        if (!$content || !isset($content['blocks'])) {
            return;
        }

        foreach ($content['blocks'] as $block) {
            $this->processBlockForDiscord($block, $webhookUrl, $textBuffer);
        }
        
        // Send any remaining text
        if (trim($textBuffer)) {
            $messages = $this->splitDiscordMessage($textBuffer);
            foreach ($messages as $msg) {
                Http::post($webhookUrl, ['content' => $msg]);
                usleep(300000);
            }
            $textBuffer = '';
        }
    }

    private function processBlockForDiscord($block, $webhookUrl, &$textBuffer)
    {
        $type = $block['type'] ?? 'paragraph';
        $data = $block['data'] ?? [];
        
        // Handle blocks with nested children (custom_section, patch_notes_section) as embeds
        if (in_array($type, ['custom_section', 'patch_notes_section'])) {
            // Flush any accumulated text before sending the embed
            if (trim($textBuffer)) {
                $messages = $this->splitDiscordMessage($textBuffer);
                foreach ($messages as $msg) {
                    Http::post($webhookUrl, ['content' => $msg]);
                    usleep(300000);
                }
                $textBuffer = '';
            }
            
            // Create and send section as embed
            $this->sendSectionAsEmbed($block, $webhookUrl);
            return;
        }
        
        // Handle images - flush buffer first, then send image
        if ($type === 'image') {
            // Send accumulated text
            if (trim($textBuffer)) {
                $messages = $this->splitDiscordMessage($textBuffer);
                foreach ($messages as $msg) {
                    Http::post($webhookUrl, ['content' => $msg]);
                    usleep(300000);
                }
                $textBuffer = '';
            }
            
            // Send image
            $imageUrl = $data['url'] ?? $data['file']['url'] ?? '';
            if ($imageUrl) {
                if (!str_starts_with($imageUrl, 'http')) {
                    $imageUrl = url($imageUrl);
                }
                $caption = $data['caption'] ?? '';
                $imageMessage = $imageUrl;
                if ($caption) {
                    $imageMessage .= "\n*" . strip_tags($caption) . "*";
                }
                Http::post($webhookUrl, ['content' => $imageMessage]);
                usleep(300000);
            }
            return;
        }
        
        // Handle tables - send as separate message
        if ($type === 'table') {
            // Send accumulated text first
            if (trim($textBuffer)) {
                $messages = $this->splitDiscordMessage($textBuffer);
                foreach ($messages as $msg) {
                    Http::post($webhookUrl, ['content' => $msg]);
                    usleep(300000);
                }
                $textBuffer = '';
            }
            
            // Send table
            $tableText = $this->formatTableForDiscord($data);
            if ($tableText) {
                Http::post($webhookUrl, ['content' => $tableText]);
                usleep(300000);
            }
            return;
        }
        
        // All other blocks - accumulate as text
        $blockText = $this->convertBlockToDiscord($block);
        if ($blockText) {
            $textBuffer .= ($textBuffer ? "\n\n" : '') . $blockText;
        }
    }

    private function sendSectionAsEmbed($block, $webhookUrl)
    {
        $type = $block['type'] ?? 'paragraph';
        $data = $block['data'] ?? [];
        
        // Determine section title and color
        if ($type === 'custom_section') {
            $title = $data['title'] ?? 'Section';
            $tag = $data['tag'] ?? '';
            $sectionTitle = $tag ? "[$tag] $title" : $title;
            $color = 5814783; // Blue color
        } else {
            $sectionTitle = "🔧 PATCH NOTES";
            $color = 15158332; // Red color
        }
        
        $children = $data['children'] ?? [];
        $description = '';
        $currentFieldContent = '';
        $images = [];
        
        foreach ($children as $child) {
            $childType = $child['type'] ?? 'paragraph';
            $childData = $child['data'] ?? [];
            
            // Collect images to display in embeds
            if ($childType === 'image') {
                // Flush current field content
                if (trim($currentFieldContent)) {
                    $description .= ($description ? "\n\n" : '') . trim($currentFieldContent);
                    $currentFieldContent = '';
                }
                
                // Store image for later
                $imageUrl = $childData['url'] ?? $childData['file']['url'] ?? '';
                if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
                    $imageUrl = url($imageUrl);
                }
                if ($imageUrl) {
                    $caption = $childData['caption'] ?? '';
                    $images[] = [
                        'url' => $imageUrl,
                        'caption' => $caption
                    ];
                }
                continue;
            }
            
            // Handle tables in embeds
            if ($childType === 'table') {
                // Flush current content
                if (trim($currentFieldContent)) {
                    $description .= ($description ? "\n\n" : '') . trim($currentFieldContent);
                    $currentFieldContent = '';
                }
                
                // Add table as field
                $tableContent = $this->formatTableForEmbed($childData);
                if ($tableContent && strlen($description . $tableContent) < 4000) {
                    $description .= ($description ? "\n\n" : '') . $tableContent;
                }
                continue;
            }
            
            // Regular text content
            $childText = $this->convertBlockToDiscord($child);
            if ($childText) {
                $currentFieldContent .= ($currentFieldContent ? "\n\n" : '') . $childText;
            }
        }
        
        // Add remaining content to description
        if (trim($currentFieldContent)) {
            $description .= ($description ? "\n\n" : '') . trim($currentFieldContent);
        }
        
        // Truncate description if too long
        if (strlen($description) > 4000) {
            $description = substr($description, 0, 3997) . '...';
        }
        
        // Send main embed with first image
        $embed = [
            'title' => $sectionTitle,
            'description' => $description ?: 'No content',
            'color' => $color
        ];
        
        // Add first image to the main embed
        if (!empty($images)) {
            $firstImage = array_shift($images);
            $embed['image'] = ['url' => $firstImage['url']];
            if ($firstImage['caption']) {
                $embed['footer'] = ['text' => strip_tags($firstImage['caption'])];
            }
        }
        
        Http::post($webhookUrl, ['embeds' => [$embed]]);
        usleep(300000);
        
        // Send additional images as separate embeds
        foreach ($images as $img) {
            $imageEmbed = [
                'color' => $color,
                'image' => ['url' => $img['url']]
            ];
            if ($img['caption']) {
                $imageEmbed['description'] = '*' . strip_tags($img['caption']) . '*';
            }
            Http::post($webhookUrl, ['embeds' => [$imageEmbed]]);
            usleep(300000);
        }
    }

    private function formatTableForEmbed($data)
    {
        $content = $data['content'] ?? [];
        $withHeadings = $data['withHeadings'] ?? false;
        
        if (empty($content)) {
            return '';
        }
        
        $tableText = "```\n";
        
        foreach ($content as $rowIndex => $row) {
            $cells = array_map(function($cell) {
                return str_pad(strip_tags($cell ?? ''), 15);
            }, $row);
            
            $tableText .= implode(' | ', $cells) . "\n";
            
            if ($withHeadings && $rowIndex === 0) {
                $tableText .= str_repeat('-', count($row) * 18) . "\n";
            }
        }
        
        $tableText .= "```";
        return $tableText;
    }

    private function formatTableForDiscord($data)
    {
        $content = $data['content'] ?? [];
        $withHeadings = $data['withHeadings'] ?? false;
        
        if (empty($content)) {
            return '';
        }
        
        $tableText = "📊 **Table:**\n```\n";
        
        foreach ($content as $rowIndex => $row) {
            $cells = array_map(function($cell) {
                return str_pad(strip_tags($cell ?? ''), 20);
            }, $row);
            
            $tableText .= implode(' | ', $cells) . "\n";
            
            if ($withHeadings && $rowIndex === 0) {
                $tableText .= str_repeat('-', count($row) * 23) . "\n";
            }
        }
        
        $tableText .= "```";
        return $tableText;
    }


    private function splitDiscordMessage($message, $limit = 1900)
    {
        if (strlen($message) <= $limit) {
            return [$message];
        }
        
        $messages = [];
        $lines = explode("\n", $message);
        $current = '';
        
        foreach ($lines as $line) {
            if (strlen($current . "\n" . $line) > $limit) {
                if ($current) {
                    $messages[] = $current;
                    $current = $line;
                } else {
                    // Single line is too long, split it
                    $messages[] = substr($line, 0, $limit);
                    $current = substr($line, $limit);
                }
            } else {
                $current .= ($current ? "\n" : '') . $line;
            }
        }
        
        if ($current) {
            $messages[] = $current;
        }
        
        return $messages;
    }

    private function convertContentToDiscord($content)
    {
        if (!$content || !isset($content['blocks'])) {
            return '';
        }

        $output = [];
        
        foreach ($content['blocks'] as $block) {
            $blockText = $this->convertBlockToDiscord($block);
            if ($blockText) {
                $output[] = $blockText;
            }
        }

        return implode("\n\n", $output);
    }

    private function convertBlockToDiscord($block)
    {
        $type = $block['type'] ?? 'paragraph';
        $data = $block['data'] ?? [];

        switch($type) {
            case 'header':
                $level = $data['level'] ?? 2;
                $text = $data['text'] ?? '';
                $prefix = str_repeat('#', min($level, 3));
                return "$prefix **$text**";

            case 'paragraph':
                return $data['text'] ?? '';

            case 'list':
                $items = $data['items'] ?? [];
                $style = $data['style'] ?? 'unordered';
                $listText = [];
                foreach ($items as $index => $item) {
                    if ($style === 'ordered') {
                        $listText[] = ($index + 1) . ". $item";
                    } else {
                        $listText[] = "• $item";
                    }
                }
                return implode("\n", $listText);

            case 'code':
                $code = $data['code'] ?? '';
                return "```\n$code\n```";

            case 'alert':
                $type = $data['type'] ?? 'info';
                $message = $data['message'] ?? '';
                $icons = [
                    'info' => 'ℹ️',
                    'warning' => '⚠️',
                    'success' => '✅',
                    'danger' => '❌'
                ];
                $icon = $icons[$type] ?? 'ℹ️';
                return "$icon **Alert:** $message";

            case 'callout':
                $title = $data['title'] ?? '';
                $message = $data['message'] ?? '';
                $type = $data['type'] ?? 'info';
                $icons = [
                    'info' => 'ℹ️',
                    'tip' => '💡',
                    'warning' => '⚠️',
                    'important' => '❗',
                    'new' => '✨'
                ];
                $icon = $icons[$type] ?? 'ℹ️';
                return "$icon **$title**\n$message";

            case 'osrs_header':
                $header = $data['header'] ?? '';
                $subheader = $data['subheader'] ?? '';
                $text = "**>>> $header**";
                if ($subheader) {
                    $text .= "\n*$subheader*";
                }
                return $text;

            case 'patch_notes_section':
            case 'custom_section':
                // Sections with children are handled recursively in processBlockForDiscord
                return '';

            case 'separator':
                // Return empty for separators - they're handled contextually
                return '';

            case 'image':
                // Images are handled separately in processBlockForDiscord
                return '';

            case 'table':
                // Tables are handled separately in processBlockForDiscord
                return '';

            case 'quote':
                $text = $data['text'] ?? '';
                $caption = $data['caption'] ?? '';
                $alignment = $data['alignment'] ?? 'left';
                
                $quoteText = "> " . str_replace("\n", "\n> ", $text);
                if ($caption) {
                    $quoteText .= "\n— *$caption*";
                }
                return $quoteText;

            case 'delimiter':
                return "✦ ✦ ✦";

            case 'raw':
                return $data['html'] ?? '';

            default:
                return '';
        }
    }
}
