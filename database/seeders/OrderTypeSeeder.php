<?php

namespace Database\Seeders;

use App\Models\OrderType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OrderType::create(['name' => 'AO']);
        OrderType::create(['name' => 'MO']);
        OrderType::create(['name' => 'Migration']);
    }
}