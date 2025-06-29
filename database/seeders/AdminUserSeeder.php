<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'SabiStore Admin',
            'email' => 'admin@sabistore.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '+2348000000000',
            'membership_active' => true,
            'membership_paid_at' => now(),
            'email_verified_at' => now(),
        ]);
    }
}
