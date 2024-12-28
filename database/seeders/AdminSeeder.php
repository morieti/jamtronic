<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        Admin::create([
            'full_name' => 'Super Admin',
            'mobile' => '09036032356',
            'role' => Admin::ROLE_SUPER_ADMIN,
            'last_login' => now(),
        ]);

        Admin::create([
            'full_name' => 'Super Admin 2',
            'mobile' => '09123553854',
            'role' => Admin::ROLE_SUPER_ADMIN,
            'last_login' => now(),
        ]);

        Admin::create([
            'full_name' => 'Support Admin',
            'mobile' => '09000000000',
            'role' => Admin::ROLE_SUPPORT,
            'last_login' => now(),
        ]);
    }
}
