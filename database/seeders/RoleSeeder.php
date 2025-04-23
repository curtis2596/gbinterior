<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::where("code", "system_admin")->first();

        if (!$role)
        {
            Role::create([
                'code' => 'system_admin',
                'name' => 'System Admin',
                'is_admin' => 1,
                'is_active' => 1,
                'is_pre_defined' => 1
            ]);
        }

        $role = Role::where("code", "developer")->first();

        if (!$role)
        {
            Role::create([
                'code' => 'developer',
                'name' => 'Developer',
                'is_admin' => 0,
                'is_active' => 1,
                'is_pre_defined' => 1
            ]);
        }
    }
}
