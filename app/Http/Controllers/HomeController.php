<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Update;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        $events = Event::notEnded()
            ->orderBy('start_at', 'asc')
            ->get();

        $updates = Update::where('is_published', true)
            ->whereNull('attached_to_update_id')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get();

        $topVotersWeek = $this->getTopVoters('week');
        $topVotersMonth = $this->getTopVoters('month');

        return view('home', compact('events', 'updates', 'topVotersWeek', 'topVotersMonth'));
    }

    public function events()
    {
        $events = Event::orderBy('start_at', 'desc')
            ->paginate(12);

        return view('events', compact('events'));
    }

    public function updates()
    {
        $updates = Update::where('is_published', true)
            ->whereNull('attached_to_update_id')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('updates.index', compact('updates'));
    }

    public function showUpdate($slug)
    {
        $update = Update::where('slug', $slug)->with('hotfixes')->firstOrFail();
        return view('updates.show', compact('update'));
    }

    private function getTopVoters($period)
    {
        $startDate = $period === 'week' 
            ? Carbon::now()->startOfWeek()
            : Carbon::now()->startOfMonth();

        return Vote::select('username', DB::raw('COUNT(*) as votes'))
            ->where('callback_date', '>=', $startDate)
            ->whereNotNull('callback_date')
            ->groupBy('username')
            ->orderBy('votes', 'desc')
            ->limit(5)
            ->get();
    }
}
