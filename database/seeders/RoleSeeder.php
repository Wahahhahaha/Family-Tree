<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
         = [
            ['roleid' => 1, 'rolename' => 'Superadmin'],
            ['roleid' => 2, 'rolename' => 'Admin'],
        ];

        foreach ( as ) {
            DB::table('role')->updateOrInsert(['roleid' => ['roleid']], );
        }
    }
}