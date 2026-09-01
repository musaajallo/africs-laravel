<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Rbac;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        User::firstOrCreate(
            ['email' => 'admin@africs.test'],
            [
                'name' => 'Africs Admin',
                'username' => 'admin',
                'password' => 'password',
            ],
        )->syncRoles([Rbac::ROLE_SUPER_ADMIN]);
    }
}
