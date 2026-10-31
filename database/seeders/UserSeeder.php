<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use LogicException;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            $name = config('seeding.initial_admin.name');
            $password = config('seeding.initial_admin.password');

            if (! is_string($name) || trim($name) === '' || ! is_string($password) || strlen($password) < 16) {
                throw new LogicException('Set INITIAL_ADMIN_NAME and an INITIAL_ADMIN_PASSWORD of at least 16 characters before seeding outside local or testing.');
            }

            User::firstOrCreate(
                ['name' => $name],
                [
                    'service' => 'SNR',
                    'password' => $password,
                    'must_change_password' => true,
                ],
            );

            return;
        }

        $users = [
            [
                'name' => 'Snr admin',
                'service' => 'SNR',
                'password' => bcrypt('net & regule'),
            ],
            [
                'name' => 'chef scu',
                'service' => 'SCU',
                'password' => bcrypt('service cuisine'),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
