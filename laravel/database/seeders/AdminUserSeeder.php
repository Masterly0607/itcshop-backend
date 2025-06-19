<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // A seeder is a file that lets you insert fake or default data into your database automatically.
        // Insert data to user model
        User::create([
            'name' =>'Admin',
            'email' =>'admin@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('admin123'),
    
        ]);
    }
}
