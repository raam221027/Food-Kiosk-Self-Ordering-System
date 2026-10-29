<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Demo accounts for the public portfolio deployment.
     */
    public function run(): void
    {
        $password = Hash::make(env('DEMO_PASSWORD', 'demo1234'));

        foreach (['Admin', 'Cashier', 'Kitchen'] as $role) {
            User::updateOrCreate(
                ['email' => strtolower($role) . '@demo.com'],
                ['name' => 'Demo ' . $role, 'password' => $password, 'role' => $role, 'is_disabled' => false]
            );
        }
    }
}
