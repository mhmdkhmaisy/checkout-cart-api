<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VoteSite;

class VoteSiteSeeder extends Seeder
{
    public function run()
    {
        $sites = [
            // TopG
            // Callback: p_resp={incentive}&ip={voter_ip}
            // Example: https://topg.org/Runescape/server-419541-username
            [
                'title' => 'TopG',
                'url' => 'https://topg.org/Runescape/server-{sid}-{incentive}',
                'site_id' => '419541',
                'active' => true,
            ],
            
            // Top100Arena
            // Callback: postback={incentive}
            // Example: https://www.top100arena.com/listing/88957/vote?incentive=username
            [
                'title' => 'Top100Arena',
                'url' => 'https://www.top100arena.com/listing/{sid}/vote?incentive={incentive}',
                'site_id' => '88957',
                'active' => true,
            ],
            
            // Rulocus (Runelocus)
            // Callback: callback={incentive}&ip={voter_ip}&secret={your_secret}
            // Example: https://www.rulocus.com/top-rsps-list/yourserver/vote?callback=username
            [
                'title' => 'Rulocus',
                'url' => 'https://www.rulocus.com/top-rsps-list/{sid}/vote?callback={incentive}',
                'site_id' => 'yourserver',
                'active' => true,
            ],
            
            // RSPS-List
            // Callback: secret={api_secret}&voted={0|1}&userip={voter_ip}&userid={incentive}
            // Example: https://www.rsps-list.com/index.php?a=in&u=Azanku&id=username
            [
                'title' => 'RSPS-List',
                'url' => 'https://www.rsps-list.com/index.php?a=in&u={sid}&id={incentive}',
                'site_id' => 'Azanku',
                'active' => true,
            ],
        ];

        foreach ($sites as $site) {
            VoteSite::create($site);
        }
    }
}