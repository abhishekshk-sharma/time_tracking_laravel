<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SuperAdmin;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        SuperAdmin::create([
            'username' => 'superadmin',
            'email' => 'superadmin@company.com',
            'password' => Hash::make('superadmin123'),
            'name' => 'Super Administrator',
            'is_active' => true,
        ]);
    }
}