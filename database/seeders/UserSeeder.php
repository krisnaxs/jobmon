<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@jobmonitor.test',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'division' => null,
                'is_active' => true,
            ]
        );
        $techniciansCctv = [
            ['name' => 'Hasan', 'username' => 'hasan'],
            ['name' => 'Jaya', 'username' => 'jaya'],
            ['name' => 'Slamet', 'username' => 'slamet'],
            ['name' => 'Ucup', 'username' => 'ucup'],
        ];

        foreach ($techniciansCctv as $t) {
            User::updateOrCreate(
                ['username' => $t['username']],
                [
                    'name' => $t['name'],
                    'email' => $t['username'] . '@jobmonitor.test',
                    'password' => Hash::make('password'),
                    'role' => 'technician',
                    'division' => 'cctv',
                    'is_active' => true,
                ]
            );
        }
        $techniciansHelpdesk = [
            ['name' => 'Ruslan', 'username' => 'ruslan'],
            ['name' => 'Darmawan', 'username' => 'darmawan'],
            ['name' => 'Rizki', 'username' => 'rizki'],
            ['name' => 'Sapta', 'username' => 'sapta'],
        ];

        foreach ($techniciansHelpdesk as $t) {
            User::updateOrCreate(
                ['username' => $t['username']],
                [
                    'name' => $t['name'],
                    'email' => $t['username'] . '@jobmonitor.test',
                    'password' => Hash::make('password'),
                    'role' => 'technician',
                    'division' => 'helpdesk',
                    'is_active' => true,
                ]
            );
        }
    }
}
