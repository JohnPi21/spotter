<?php

namespace Database\Seeders;

use App\Enums\RolesEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name'              => 'John Alex',
            'email'             => 'imalex96ro@gmail.com',
            'password'          => Hash::make(config('seeder.master_password')),
            'email_verified_at' => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $user->assignRole(RolesEnum::ADMIN);
    }
}
