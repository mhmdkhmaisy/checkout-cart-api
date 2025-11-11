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
            // Convert update content to Discord format
            $content = json_decode($update->content, true);
            $formattedContent = $this->convertContentToDiscord($content);
            
            // Build the message
            $updateUrl = route('updates.show', $update->slug);
            $message = "📢 **New Update: {$update->title}**\n\n";
            
            if ($update->category) {
                $message .= "**Category:** {$update->category}\n";
            }
            
            if ($update->author) {
                $message .= "**Author:** {$update->author}\n";
            }
            
            $message .= "\n━━━━━━━━━━━━━━━━━━\n\n";
            $message .= $formattedContent;
            $message .= "\n\n━━━━━━━━━━━━━━━━━━\n";
            $message .= "🔗 **Read full update:** {$updateUrl}";
            
            // Discord has a 2000 character limit per message
            // Split if necessary
            $messages = $this->splitDiscordMessage($message);
            
            foreach ($messages as $msg) {
                $response = Http::post($webhookUrl, [
                    'content' => $msg
                ]);
                
                if (!$response->successful()) {
                    throw new \Exception('Discord API error: ' . $response->body());
                }
                
                // Small delay between messages to avoid rate limiting
                if (count($messages) > 1) {
                    usleep(500000); // 0.5 second delay
                }
            }
            
            return redirect()->back()->with('success', 'Update sent to Discord successfully!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send to Discord: ' . $e->getMessage());
        }
    }

    public function showScreenshotView(Update $update)
    {
        return view('updates.screenshot', compact('update'));
    }

    public function processScreenshot(Request $request)
    {
        $request->validate([
            'screenshot' => 'required|file|mimes:png,jpg,jpeg|max:10240',
            'update_id' => 'required|exists:updates,id'
        ]);

        $webhookUrl = config('services.discord.webhook_url');
        $update = Update::findOrFail($request->update_id);
        
        try {
            $screenshotFile = $request->file('screenshot');
            $updateUrl = route('updates.show', $update->slug);
            
            // Send to Discord with image attachment
            $response = Http::attach(
                'file',
                file_get_contents($screenshotFile->getRealPath()),
                'update-screenshot.png'
            )->post($webhookUrl, [
                'content' => "📢 **New Update: {$update->title}**\n\n🔗 Read more: {$updateUrl}"
            ]);

            if ($response->successful()) {
                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false, 'error' => 'Discord API error'], 500);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
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
                $children = $data['children'] ?? [];
                $text = "🔧 **PATCH NOTES**\n";
                foreach ($children as $child) {
                    $childText = $this->convertBlockToDiscord($child);
                    if ($childText) {
                        $text .= $childText . "\n";
                    }
                }
                return $text;

            case 'custom_section':
                $title = $data['title'] ?? 'Section';
                $tag = $data['tag'] ?? 'SECTION';
                $children = $data['children'] ?? [];
                $text = "`$tag` **$title**\n";
                foreach ($children as $child) {
                    $childText = $this->convertBlockToDiscord($child);
                    if ($childText) {
                        $text .= $childText . "\n";
                    }
                }
                return $text;

            case 'separator':
                return "━━━━━━━━━━━━━━━━━━";

            case 'image':
                $url = $data['url'] ?? $data['file']['url'] ?? '';
                $caption = $data['caption'] ?? '';
                if ($url) {
                    // Make sure URL is absolute
                    if (!str_starts_with($url, 'http')) {
                        $url = url($url);
                    }
                    $text = "🖼️ **Image:** $url";
                    if ($caption) {
                        $text .= "\n*$caption*";
                    }
                    return $text;
                }
                return '';

            case 'table':
                $content = $data['content'] ?? [];
                $withHeadings = $data['withHeadings'] ?? false;
                
                if (empty($content)) {
                    return '';
                }
                
                $tableText = "📊 **Table:**\n```\n";
                
                foreach ($content as $rowIndex => $row) {
                    $cells = array_map(function($cell) {
                        // Strip HTML tags and limit length
                        return str_pad(strip_tags($cell ?? ''), 20);
                    }, $row);
                    
                    $tableText .= implode(' | ', $cells) . "\n";
                    
                    // Add separator after header if withHeadings is true
                    if ($withHeadings && $rowIndex === 0) {
                        $tableText .= str_repeat('-', count($row) * 23) . "\n";
                    }
                }
                
                $tableText .= "```";
                return $tableText;

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
