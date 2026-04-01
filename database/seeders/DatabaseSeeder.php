<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Website\database\Seeders\WebsiteDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // RolesAndPermissionsSeeder::class,
            //UserDemoRoles::class,
            WebsiteDatabaseSeeder::class
        ]);

    }
}
