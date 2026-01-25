<?php

namespace App\Http\Controllers;

use App\Models\ReferralLink;
use App\Models\ReferralClick;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function track(Request $request, $code)
    {
        $link = ReferralLink::where('code', $code)->where('is_active', true)->firstOrFail();

        ReferralClick::create([
            'referral_link_id' => $link->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
        ]);

        return redirect()->to($link->target_url);
    }
}
