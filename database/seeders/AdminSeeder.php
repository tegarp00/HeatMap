<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Admin::query()->create([
            "username" => "mimin",
            "email" => "mimin@heatmap.com",
            "password" => "whoami1234"
        ]);
    }
}
