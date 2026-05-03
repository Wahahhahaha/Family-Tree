<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LevelSeeder extends Seeder
{
    public function run(): void
    {
         = [
            ['levelid' => 1, 'levelname' => 'Employer'],
            ['levelid' => 2, 'levelname' => 'Family Member'],
        ];

        foreach ( as ) {
            DB::table('level')->updateOrInsert(['levelid' => ['levelid']], );
        }
    }
}