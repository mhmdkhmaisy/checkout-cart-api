<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TeamMemberController extends Controller
{
    public function index(): View
    {
        $teamMembers = TeamMember::withCount('payouts')
            ->withSum('payouts as total_paid', 'payout_amount')
            ->orderBy('name')
            ->get();
        
        $totalPercentage = TeamMember::active()->sum('percentage');
        
        $payoutStats = [
            'total_payouts' => Payout::count(),
            'completed_payouts' => Payout::completed()->count(),
            'pending_payouts' => Payout::pending()->count(),
            'failed_payouts' => Payout::failed()->count(),
            'total_paid_out' => Payout::completed()->sum('payout_amount'),
        ];
        
        return view('admin.team-members.index', compact('teamMembers', 'totalPercentage', 'payoutStats'));
    }

    public function create(): View
    {
        $currentTotal = TeamMember::active()->sum('percentage');
        $maxAllowed = 100 - $currentTotal;
        
        return view('admin.team-members.create', compact('currentTotal', 'maxAllowed'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'paypal_email' => 'required|email|unique:team_members,paypal_email',
            'percentage' => 'required|numeric|min:0.01|max:100',
        ]);

        $currentTotal = TeamMember::active()->sum('percentage');
        if (($currentTotal + $validated['percentage']) > 100) {
            return back()->withErrors(['percentage' => "Adding {$validated['percentage']}% would exceed 100%. Current total is {$currentTotal}%."])->withInput();
        }

        TeamMember::create($validated);

        return redirect()->route('admin.team-members.index')->with('success', 'Team member added successfully.');
    }

    public function edit(TeamMember $teamMember): View
    {
        $currentTotal = TeamMember::active()->where('id', '!=', $teamMember->id)->sum('percentage');
        $maxAllowed = 100 - $currentTotal;
        
        return view('admin.team-members.edit', compact('teamMember', 'currentTotal', 'maxAllowed'));
    }

    public function update(Request $request, TeamMember $teamMember): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'paypal_email' => 'required|email|unique:team_members,paypal_email,' . $teamMember->id,
            'percentage' => 'required|numeric|min:0.01|max:100',
            'is_active' => 'boolean',
        ]);

        $currentTotal = TeamMember::active()->where('id', '!=', $teamMember->id)->sum('percentage');
        $newTotal = $currentTotal + $validated['percentage'];
        
        if ($newTotal > 100 && ($validated['is_active'] ?? $teamMember->is_active)) {
            return back()->withErrors(['percentage' => "This would set total to {$newTotal}% which exceeds 100%."])->withInput();
        }

        $teamMember->update($validated);

        return redirect()->route('admin.team-members.index')->with('success', 'Team member updated successfully.');
    }

    public function destroy(TeamMember $teamMember): RedirectResponse
    {
        if ($teamMember->payouts()->exists()) {
            $teamMember->update(['is_active' => false]);
            return redirect()->route('admin.team-members.index')->with('warning', 'Team member deactivated (has payout history).');
        }

        $teamMember->delete();
        return redirect()->route('admin.team-members.index')->with('success', 'Team member deleted successfully.');
    }

    public function toggleActive(TeamMember $teamMember): RedirectResponse
    {
        if (!$teamMember->is_active) {
            $currentTotal = TeamMember::active()->sum('percentage');
            if (($currentTotal + $teamMember->percentage) > 100) {
                return back()->withErrors(['error' => "Cannot activate: would exceed 100% total."]);
            }
        }

        $teamMember->update(['is_active' => !$teamMember->is_active]);
        
        $status = $teamMember->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.team-members.index')->with('success', "Team member {$status}.");
    }

    public function payouts(): View
    {
        $payouts = Payout::with(['order', 'teamMember'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        return view('admin.team-members.payouts', compact('payouts'));
    }
}
