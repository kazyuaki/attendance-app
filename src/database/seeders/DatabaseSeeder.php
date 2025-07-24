<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::factory(10)->create();
        \App\Models\User::factory(10)->create();

        \App\Models\Attendance::factory(20)->create();
        \App\Models\BreakTime::factory(30)->create();

        $this->call(UserAndAdminSeeder::class);
    }
}
