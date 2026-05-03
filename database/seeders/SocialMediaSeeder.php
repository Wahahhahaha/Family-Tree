<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SocialMediaSeeder extends Seeder
{
    public function run(): void
    {
         = [
            ['socialid' => 1, 'socialname' => 'Instagram', 'socialicon' => 'instagram'],
            ['socialid' => 2, 'socialname' => 'Facebook', 'socialicon' => 'facebook'],
            ['socialid' => 3, 'socialname' => 'Youtube', 'socialicon' => 'youtube'],
            ['socialid' => 4, 'socialname' => 'LinkedIn', 'socialicon' => 'linkedin'],
            ['socialid' => 5, 'socialname' => 'Tiktok', 'socialicon' => 'tiktok'],
            ['socialid' => 6, 'socialname' => 'Snapchat', 'socialicon' => 'snapchat'],
            ['socialid' => 7, 'socialname' => 'Telegram', 'socialicon' => 'telegram'],
            ['socialid' => 8, 'socialname' => 'X', 'socialicon' => 'x'],
            ['socialid' => 9, 'socialname' => 'Thread', 'socialicon' => 'threads'],
            ['socialid' => 10, 'socialname' => 'Quora', 'socialicon' => 'quora'],
            ['socialid' => 11, 'socialname' => 'Wechat', 'socialicon' => 'wechat'],
            ['socialid' => 12, 'socialname' => 'Reddit', 'socialicon' => 'reddit'],
            ['socialid' => 13, 'socialname' => 'Discord', 'socialicon' => 'discord'],
            ['socialid' => 14, 'socialname' => 'Twitch', 'socialicon' => 'twitch'],
            ['socialid' => 15, 'socialname' => 'Pinterest', 'socialicon' => 'pinterest'],
            ['socialid' => 16, 'socialname' => 'Line', 'socialicon' => 'line'],
        ];

        foreach ( as ) {
            DB::table('socialmedia')->updateOrInsert(['socialid' => ['socialid']], );
        }
    }
}