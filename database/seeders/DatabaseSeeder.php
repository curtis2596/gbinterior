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
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(LedgerCategorySeeder::class);
        $this->call(LedgerAccountSeeder::class);
        $this->call(AutoIncreamentSeeder::class);
        $this->call(CompanySeeder::class);
    }
}
