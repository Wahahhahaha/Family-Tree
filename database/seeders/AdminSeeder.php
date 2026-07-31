<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $username = 'superadmin';

        if (! DB::table('user')->where('username', $username)->exists()) {
            $userId = DB::table('user')->insertGetId([
                'username' => $username,
                'password' => Hash::make('password'),
                'levelid' => 1,
            ]);

            DB::table('employer')->insert([
                'name' => 'Super Admin',
                'email' => 'admin@example.com',
                'phonenumber' => '000',
                'roleid' => 1,
                'userid' => $userId,
                'created_at' => now(),
            ]);

            $this->command->info('Superadmin created with password: password');
        } else {
            $this->command->warn('Superadmin already exists.');
        }
    }
}
