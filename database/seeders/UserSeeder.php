<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users_arr = [
            'Administrator' => 'admin@admin',
            'John Doe'      => 'john@test',
            'User 1'        => 'test1@test',
            'User 2'        => 'test2@test',
            'User 3'        => 'test3@test',
        ];

        foreach($users_arr as $name => $email) {
            $user = new User([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('p4ssw0rd'),
            ]);
            $user->save();

            $user->assignRole('superadmin');
        }
    }
}
