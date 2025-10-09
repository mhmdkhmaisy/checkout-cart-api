<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vote;
use App\Models\VoteSite;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VoteAdminController extends Controller
{

    public function index()
    {
        $sites = VoteSite::withCount([
            'votes',
            'votes as completed_votes_count' => function($query) {
                $query->whereNotNull('callback_date');
            },
            'votes as today_votes_count' => function($query) {
                $query->whereNotNull('callback_date')
                      ->whereDate('callback_date', Carbon::today());
            }
        ])->get();

        $stats = [
            'total_sites' => VoteSite::count(),
            'active_sites' => VoteSite::where('active', true)->count(),
            'total_votes' => Vote::whereNotNull('callback_date')->count(),
            'today_votes' => Vote::whereNotNull('callback_date')->whereDate('callback_date', Carbon::today())->count(),
        ];

        return view('admin.vote.index', compact('sites', 'stats'));
    }

    public function sites()
    {
        $sites = VoteSite::withCount([
            'votes',
            'votes as completed_votes_count' => function($query) {
                $query->whereNotNull('callback_date');
            }
        ])->orderBy('id')->get();

        return view('admin.vote.sites', compact('sites'));
    }

    public function createSite()
    {
        return view('admin.vote.create-site');
    }

    public function storeSite(Request $request)
    {
        $request->validate([
            'title' => 'required|string|min:3|max:255',
            'site_id' => 'required|string|min:1|max:255',
            'url' => 'required|url|max:1000',
            'active' => 'boolean'
        ]);

        VoteSite::create([
            'title' => $request->title,
            'site_id' => $request->site_id,
            'url' => $request->url,
            'active' => $request->boolean('active', true)
        ]);

        return redirect()->route('admin.vote.sites')
            ->with('success', 'Vote site created successfully!');
    }

    public function editSite(VoteSite $site)
    {
        return view('admin.vote.edit-site', compact('site'));
    }

    public function updateSite(Request $request, VoteSite $site)
    {
        $request->validate([
            'title' => 'required|string|min:3|max:255',
            'site_id' => 'required|string|min:1|max:255',
            'url' => 'required|url|max:1000',
            'active' => 'boolean'
        ]);

        $site->update([
            'title' => $request->title,
            'site_id' => $request->site_id,
            'url' => $request->url,
            'active' => $request->boolean('active')
        ]);

        return redirect()->route('admin.vote.sites')
            ->with('success', 'Vote site updated successfully!');
    }

    public function toggleSite(VoteSite $site)
    {
        $site->update(['active' => !$site->active]);

        $status = $site->active ? 'activated' : 'deactivated';
        return redirect()->back()
            ->with('success', "Vote site {$status} successfully!");
    }

    public function destroySite(VoteSite $site)
    {
        $site->delete();

        return redirect()->route('admin.vote.sites')
            ->with('success', 'Vote site deleted successfully!');
    }

    public function votes(Request $request)
    {
        $query = Vote::with('site');

        if ($request->filled('username')) {
            $query->where('username', 'like', '%' . $request->username . '%');
        }

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'completed') {
                $query->whereNotNull('callback_date');
            } elseif ($request->status === 'pending') {
                $query->whereNull('callback_date');
            } elseif ($request->status === 'claimed') {
                $query->where('claimed', true);
            } elseif ($request->status === 'unclaimed') {
                $query->where('claimed', false);
            }
        }

        $votes = $query->orderBy('started', 'desc')->paginate(20);

        $stats = [
            'total_votes' => Vote::count(),
            'completed_votes' => Vote::whereNotNull('callback_date')->count(),
            'pending_votes' => Vote::whereNull('callback_date')->count(),
            'claimed_votes' => Vote::where('claimed', true)->count(),
        ];

        return view('admin.vote.votes', compact('votes', 'stats'));
    }

    public function claimVote(Vote $vote)
    {
        if (!$vote->callback_date) {
            return redirect()->back()
                ->with('error', 'Cannot claim vote that has not been completed.');
        }

        $vote->update(['claimed' => true]);

        return redirect()->back()
            ->with('success', 'Vote marked as claimed successfully!');
    }

    public function stats()
    {
        $sites = VoteSite::withCount([
            'votes as total_votes_count',
            'votes as completed_votes_count' => function($query) {
                $query->whereNotNull('callback_date');
            },
            'votes as today_votes_count' => function($query) {
                $query->whereNotNull('callback_date')
                      ->whereDate('callback_date', Carbon::today());
            },
            'votes as week_votes_count' => function($query) {
                $query->whereNotNull('callback_date')
                      ->whereBetween('callback_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            }
        ])->get();

        $dailyStats = Vote::whereNotNull('callback_date')
            ->whereBetween('callback_date', [Carbon::now()->subDays(30), Carbon::now()])
            ->selectRaw('DATE(callback_date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topVoters = Vote::whereNotNull('callback_date')
            ->selectRaw('username, COUNT(*) as vote_count')
            ->groupBy('username')
            ->orderBy('vote_count', 'desc')
            ->take(10)
            ->get();

        return view('admin.vote.stats', compact('sites', 'dailyStats', 'topVoters'));
    }

    // Alias methods for backward compatibility
    public function create()
    {
        return view('admin.vote.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|min:3|max:255',
            'site_id' => 'required|string|min:1|max:255',
            'url' => 'required|url|max:1000',
            'active' => 'boolean'
        ]);

        VoteSite::create([
            'title' => $request->title,
            'site_id' => $request->site_id,
            'url' => $request->url,
            'active' => $request->boolean('active', true)
        ]);

        return redirect()->route('admin.vote.index')
            ->with('success', 'Vote site created successfully!');
    }

    public function show(VoteSite $voteSite)
    {
        $voteSite->load(['votes' => function($query) {
            $query->latest()->limit(50);
        }]);

        $stats = [
            'total_votes' => $voteSite->votes()->whereNotNull('callback_date')->count(),
            'pending_votes' => $voteSite->votes()->whereNull('callback_date')->count(),
            'today_votes' => $voteSite->votes()->whereNotNull('callback_date')->whereDate('callback_date', today())->count(),
            'month_votes' => $voteSite->votes()->whereNotNull('callback_date')->whereMonth('callback_date', now()->month)->count(),
        ];

        return view('admin.vote.show', compact('voteSite', 'stats'));
    }

    public function edit(VoteSite $voteSite)
    {
        return view('admin.vote.edit', compact('voteSite'));
    }

    public function update(Request $request, VoteSite $voteSite)
    {
        $request->validate([
            'title' => 'required|string|min:3|max:255',
            'site_id' => 'required|string|min:1|max:255',
            'url' => 'required|url|max:1000',
            'active' => 'boolean'
        ]);

        $voteSite->update([
            'title' => $request->title,
            'site_id' => $request->site_id,
            'url' => $request->url,
            'active' => $request->boolean('active')
        ]);

        return redirect()->route('admin.vote.index')
            ->with('success', 'Vote site updated successfully!');
    }

    public function destroy(VoteSite $voteSite)
    {
        $voteSite->delete();

        return redirect()->route('admin.vote.index')
            ->with('success', 'Vote site deleted successfully!');
    }

    public function toggleActive(VoteSite $voteSite)
    {
        return $this->toggleSite($voteSite);
    }
}