<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;


class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        foreach ($users as $user) {
            $user->roles()->detach();
        }
        // use this method for relations 
        Role::whereNotNull('id')->delete();
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        Role::create(['name' => 'vendor', 'guard_name' => 'api']);
        Role::create(['name' => 'user', 'guard_name' => 'api']);
    }
}
