<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                "name" => "Snr admin",
                "service" => "SNR",
                "password" => bcrypt("net & regule")
            ],
            [
                "name" => "chef scu",
                "service" => "SCU",
                "password" => bcrypt("service cuisine")
            ]
        ];

        foreach($users as $user){
            User::create($user);
        }
    }
}
