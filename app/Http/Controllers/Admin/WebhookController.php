<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use App\Services\DiscordWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WebhookController extends Controller
{
    protected $discordWebhook;

    public function __construct(DiscordWebhookService $discordWebhook)
    {
        $this->discordWebhook = $discordWebhook;
    }

    public function index()
    {
        $webhooks = Webhook::orderBy('created_at', 'desc')->get();
        
        return view('admin.webhooks.index', compact('webhooks'));
    }

    public function create()
    {
        return view('admin.webhooks.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:128',
            'url' => 'required|url|max:512',
            'event_type' => 'required|in:promotion.created,promotion.claimed,promotion.limit_reached,update.published',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $webhook = Webhook::create([
            'name' => $request->name,
            'url' => $request->url,
            'event_type' => $request->event_type,
            'is_active' => $request->has('is_active'),
        ]);

        $this->discordWebhook->clearCache();

        return redirect()->route('admin.webhooks.index')
            ->with('success', 'Webhook created successfully!');
    }

    public function edit(Webhook $webhook)
    {
        return view('admin.webhooks.edit', compact('webhook'));
    }

    public function update(Request $request, Webhook $webhook)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:128',
            'url' => 'required|url|max:512',
            'event_type' => 'required|in:promotion.created,promotion.claimed,promotion.limit_reached,update.published',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $webhook->update([
            'name' => $request->name,
            'url' => $request->url,
            'event_type' => $request->event_type,
            'is_active' => $request->has('is_active'),
        ]);

        $this->discordWebhook->clearCache();

        return redirect()->route('admin.webhooks.index')
            ->with('success', 'Webhook updated successfully!');
    }

    public function destroy(Webhook $webhook)
    {
        $webhook->delete();

        $this->discordWebhook->clearCache();

        return redirect()->route('admin.webhooks.index')
            ->with('success', 'Webhook deleted successfully!');
    }

    public function toggleActive(Webhook $webhook)
    {
        $webhook->update(['is_active' => !$webhook->is_active]);
        
        $this->discordWebhook->clearCache();

        return redirect()->back()
            ->with('success', 'Webhook status updated!');
    }
}
