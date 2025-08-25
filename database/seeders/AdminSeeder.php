<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::truncate();
        Admin::create([
            'email' => 'admin@gmail.com',
            'name' => 'Super Admin',
            'is_active' => true,
            'role' => 'super_admin',
            'password' => bcrypt('password'),
        ]);
    }
}
