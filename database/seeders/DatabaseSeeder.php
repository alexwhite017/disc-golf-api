<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Admin user (required first — CourseSeeder depends on it)
        User::create([
            'name' => 'Admin',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        $this->call([
            CourseSeeder::class,
            UserSeeder::class,
        ]);
    }
}
