<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        foreach (range(1, 3) as $index) {
            $user = User::create([
                'name' => $faker->name(),
                'email' => $faker->unique()->email(),
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]);
            $user->assignRole('admin', 'user', 'viewer');
        }
    }
}
