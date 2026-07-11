<?php

namespace Database\Seeders;

use App\Models\Repost;
use Illuminate\Database\Seeder;

class RepostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Repost::factory()->count(20)->create();
    }
}
