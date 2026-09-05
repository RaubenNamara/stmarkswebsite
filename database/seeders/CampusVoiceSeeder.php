<?php

namespace Database\Seeders;

use App\Models\CampusVoice;
use Illuminate\Database\Seeder;

class CampusVoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CampusVoice::factory()->count(20)->create();
    }
}
