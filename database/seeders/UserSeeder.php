<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create staff users with Ghanaian context
        $users = [
            [
                'name' => 'Kwame Nkrumah',
                'email' => 'knkrumah@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            
       
            [
                'name' => 'Ama Ata Aidoo',
                'email' => 'aaidoo@example.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => $user['password'],
                    'role' => $user['role'],
                ]
            );
        }
        
  
    }
}
