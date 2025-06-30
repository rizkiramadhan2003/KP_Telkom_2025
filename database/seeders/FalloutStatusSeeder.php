<?php

namespace Database\Seeders;

use App\Models\FalloutStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FalloutStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FalloutStatus::create(['name' => 'PI']);
        FalloutStatus::create(['name' => 'FA']);
    }
}