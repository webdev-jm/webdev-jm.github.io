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

        $user = new User([
            'name' => 'Administrator',
            'email' => 'admin@admin',
            'password' => Hash::make('p4ssw0rd'),
        ]);
        $user->save();

        $user->assignRole('superadmin');
    }
}
