<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userAdmin = new User();
        $userAdmin->name  = "Darren Nienaber";
        $userAdmin->email  = "darren@empirestate.co.za";
        $userAdmin->password  = Hash::make('password');
        $userAdmin->save();
    }
}
