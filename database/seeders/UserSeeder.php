<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::whereNotNull('id')->delete();
        $faker = Faker::create();
        foreach (range(1, 10) as $index) {
            $user = User::create([
                'name' => $faker->name(),
                'email' => $faker->unique()->email(),
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]);
            $user->assignRole('user');
        }
    }
}
