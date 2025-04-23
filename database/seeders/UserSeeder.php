<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserRole;
use Exception;

class UserSeeder extends Seeder
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
            throw new Exception("System Admin Role Not Found");
        }

        $user = User::where("name", "Admin")->first();

        if (!$user)
        {
            $user = User::create([
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => bcrypt('admin@123'),
                'dont_send_email' => 1,
                'is_active' => 1,
                'is_pre_defined' => 1,
            ]);

            UserRole::create([
                "user_id" => $user->id,
                "role_id" => $role->id,
            ]);
        }
    }
}
