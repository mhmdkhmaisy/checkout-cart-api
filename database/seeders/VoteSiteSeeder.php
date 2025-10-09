<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VoteSite;

class VoteSiteSeeder extends Seeder
{
    public function run()
    {
        $sites = [
            [
                'title' => 'RuneLocus',
                'url' => 'http://www.runelocus.com/top-rsps-list/vote-{sid}/?id2={incentive}',
                'site_id' => '43451',
                'active' => true,
            ],
            [
                'title' => 'Top100Arena',
                'url' => 'http://www.top100arena.com/in.asp?id={sid}&incentive={incentive}',
                'site_id' => '88957',
                'active' => true,
            ],
            [
                'title' => 'RSPS-List',
                'url' => 'http://www.rsps-list.com/index.php?a=in&u={sid}&id={incentive}',
                'site_id' => 'Azanku',
                'active' => true,
            ],
            [
                'title' => 'Rune-Server',
                'url' => 'http://www.rune-server.org/toplist.php?do=vote&sid={sid}&incentive={incentive}',
                'site_id' => '10226',
                'active' => true,
            ],
            [
                'title' => 'TopG',
                'url' => 'http://topg.org/Runescape/in-{sid}-{incentive}',
                'site_id' => '419541',
                'active' => true,
            ],
            [
                'title' => 'RuneScript',
                'url' => 'http://www.rune-script.com/toplist.php?action=vote&id={sid}&incentive={incentive}',
                'site_id' => '8843',
                'active' => true,
            ],
        ];

        foreach ($sites as $site) {
            VoteSite::create($site);
        }
    }
}