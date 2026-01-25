<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralLink;
use App\Models\ReferralClick;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    public function index()
    {
        $links = ReferralLink::withCount(['clicks as total_clicks'])
            ->withCount(['clicks as unique_clicks' => function($query) {
                $query->select(DB::raw('count(distinct ip_address)'));
            }])
            ->get();

        return view('admin.referrals.index', compact('links'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20|unique:referral_links,code',
            'target_url' => 'required|string|max:255',
        ]);

        ReferralLink::create([
            'name' => $request->name,
            'code' => $request->code ?: Str::random(8),
            'target_url' => $request->target_url,
        ]);

        return back()->with('success', 'Referral link created successfully.');
    }

    public function show($id)
    {
        $referralLink = ReferralLink::findOrFail($id);
        $clicksByDay = ReferralClick::where('referral_link_id', $referralLink->id)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        return view('admin.referrals.show', compact('referralLink', 'clicksByDay'));
    }

    public function destroy($id)
    {
        $referralLink = ReferralLink::findOrFail($id);
        $referralLink->delete();
        return back()->with('success', 'Referral link deleted.');
    }
}
