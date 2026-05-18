<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@laundry.com',
            'phone' => '081111111111',
            'password' => \Illuminate\Support\Facades\Hash::make('jokowiikn123'),
            'role' => 'admin'
        ]);
    }
}
