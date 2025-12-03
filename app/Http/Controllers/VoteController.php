<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use App\Models\VoteSite;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VoteController extends Controller
{
    public function index()
    {
        $sites = VoteSite::where('active', true)->withCount([
            'votes',
            'votes as today_votes_count' => function($query) {
                $query->whereNotNull('callback_date')
                      ->whereDate('callback_date', Carbon::today());
            }
        ])->get();

        // Get user votes for cooldown checking
        $userVotes = [];
        if (session('vote_username')) {
            $recentVotes = Vote::where('username', session('vote_username'))
                ->where('started', '>=', Carbon::now()->subHours(12))
                ->pluck('started', 'site_id')
                ->toArray();
            $userVotes = $recentVotes;
        }

        return view('vote.index', compact('sites', 'userVotes'));
    }

    public function setUsername(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:3|max:15|regex:/^[A-Za-z0-9_]+$/'
        ]);

        $username = $request->username;

        // Store username in session
        session(['vote_username' => $username]);

        return response()->json([
            'success' => true,
            'message' => 'Username saved successfully!'
        ]);
    }

    public function vote(Request $request, VoteSite $site)
    {
        $request->validate([
            'username' => 'required|string|min:3|max:15|regex:/^[A-Za-z0-9_]+$/'
        ]);

        $username = $request->username;

        // Check if user has voted on this site in the last 12 hours
        $recentVote = Vote::where('username', $username)
            ->where('site_id', $site->id)
            ->where('started', '>=', Carbon::now()->subHours(12))
            ->first();

        if ($recentVote) {
            $nextVoteTime = Carbon::parse($recentVote->started)->addHours(12);
            return response()->json([
                'success' => false,
                'message' => "You can vote again in " . $nextVoteTime->diffForHumans()
            ]);
        }

        // Create vote record
        $vote = Vote::create([
            'username' => $username,
            'site_id' => $site->id,
            'ip_address' => $request->ip(),
            'started' => Carbon::now(),
            'uid' => uniqid()
        ]);

        // Store username in session for persistence
        session(['vote_username' => $username]);

        // Generate vote URL
        $voteUrl = str_replace(
            ['{sid}', '{incentive}'],
            [$site->site_id, $vote->uid],
            $site->url
        );

        return response()->json([
            'success' => true,
            'vote_url' => $voteUrl
        ]);
    }

    public function callback(Request $request)
    {
        // Handle vote callbacks from different voting sites
        // Each toplist sends the incentive (uid) with different parameter names:
        
        $uid = null;
        
        // TopG sends: p_resp and ip
        if ($request->has('p_resp')) {
            $uid = $request->get('p_resp');
        }
        // Top100Arena sends: postback
        elseif ($request->has('postback')) {
            $uid = $request->get('postback');
        }
        // Rulocus (Runelocus) sends: callback, ip, and secret
        elseif ($request->has('callback')) {
            $uid = $request->get('callback');
        }
        // RSPS-List & Arena-Top100 sends: userid, voted, userip, and secret
        elseif ($request->has('userid')) {
            $uid = $request->get('userid');
        }
        // Arena-Top100 also supports: id parameter
        elseif ($request->has('id')) {
            $uid = $request->get('id');
        }
        // RSPS100 sends: user (only on valid votes)
        elseif ($request->has('user')) {
            $uid = $request->get('user');
        }
        // Fallback to legacy parameter names
        elseif ($request->has('incentive')) {
            $uid = $request->get('incentive');
        }
        elseif ($request->has('uid')) {
            $uid = $request->get('uid');
        }
        
        if (!$uid) {
            return response('Missing uid parameter', 400);
        }

        $vote = Vote::where('uid', $uid)->first();
        
        if (!$vote) {
            return response('Vote not found', 404);
        }

        if ($vote->callback_date) {
            return response('Vote already processed', 200);
        }

        // For Arena-Top100 & RSPS-List: Validate secret if provided
        if ($request->has('secret')) {
            $secret = $request->get('secret');
            $arenaSecret = config('services.vote.arena_top100_secret');
            $rspsListSecret = config('services.vote.rsps_list_secret');
            
            // Allow TEST secret for development, or validate against configured secrets
            if ($secret !== 'TEST' && $secret !== $arenaSecret && $secret !== $rspsListSecret) {
                // Only reject if we have a configured secret to validate against
                if ($arenaSecret || $rspsListSecret) {
                    return response('Invalid secret', 403);
                }
            }
        }

        // For Arena-Top100 & RSPS-List: check if the vote was successful (voted=1)
        if ($request->has('voted') && $request->get('voted') != '1') {
            return response('Vote not successful', 400);
        }

        // Mark vote as completed
        $vote->update([
            'callback_date' => Carbon::now()
        ]);

        // Here you would typically give rewards to the player
        // Example: GameRewardService::giveVoteReward($vote->username);

        return response('OK', 200);
    }

    public function stats()
    {
        $stats = [
            'total_votes' => Vote::whereNotNull('callback_date')->count(),
            'today_votes' => Vote::whereNotNull('callback_date')->whereDate('callback_date', today())->count(),
            'week_votes' => Vote::whereNotNull('callback_date')->whereBetween('callback_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => Vote::whereNotNull('callback_date')->whereMonth('callback_date', now()->month)->count(),
            'active_sites' => VoteSite::where('active', true)->count(),
        ];

        // Get all sites for the stats view
        $sites = VoteSite::withCount([
            'votes as total_votes',
            'votes as completed_votes' => function($query) {
                $query->whereNotNull('callback_date');
            },
            'votes as today_votes' => function($query) {
                $query->whereNotNull('callback_date')->whereDate('callback_date', today());
            }
        ])->get();

        $siteStats = $sites; // Alias for compatibility

        $topVoters = Vote::whereNotNull('callback_date')
            ->selectRaw('username, COUNT(*) as vote_count')
            ->whereMonth('callback_date', now()->month)
            ->groupBy('username')
            ->orderBy('vote_count', 'desc')
            ->take(10)
            ->get();

        $recentVotes = Vote::whereNotNull('callback_date')
            ->with('site')
            ->orderBy('callback_date', 'desc')
            ->take(10)
            ->get();

        return view('vote.stats', compact('stats', 'sites', 'siteStats', 'topVoters', 'recentVotes'));
    }

    public function getUserVotes(Request $request)
    {
        $username = $request->get('username');
        
        if (!$username) {
            return response()->json([]);
        }

        $sites = VoteSite::where('active', true)->get();
        $result = [];

        foreach ($sites as $site) {
            $lastVote = Vote::where('username', $username)
                ->where('site_id', $site->id)
                ->orderBy('started', 'desc')
                ->first();

            $canVote = true;
            $timeRemaining = null;

            if ($lastVote) {
                $nextVoteTime = Carbon::parse($lastVote->started)->addHours(12);
                if ($nextVoteTime->isFuture()) {
                    $canVote = false;
                    $timeRemaining = $nextVoteTime->diffForHumans();
                }
            }

            $result[] = [
                'site' => $site,
                'can_vote' => $canVote,
                'time_remaining' => $timeRemaining
            ];
        }

        return response()->json($result);
    }
}