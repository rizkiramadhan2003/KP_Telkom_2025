<?php

namespace Database\Seeders;

use App\Models\HdDaman;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HdDamanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HdDaman::create(['name' => 'Daman A']);
        HdDaman::create(['name' => 'Daman B']);
        HdDaman::create(['name' => 'Daman C']);
    }
}