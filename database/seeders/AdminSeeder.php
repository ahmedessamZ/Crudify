<?php

namespace Database\Seeders;

use App\Models\Admin\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('secret'),
            'status' => 'active',
        ]);
    }
}
