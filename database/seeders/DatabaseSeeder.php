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

        $admin = User::firstOrCreate(
            ['email' => 'admin@africsinc.com'],
            [
                'name' => 'Africs Admin',
                'username' => 'admin',
                'password' => 'password',
            ],
        );

        $admin->forceFill(['email_verified_at' => $admin->email_verified_at ?? now()])->save();
        $admin->syncRoles([Rbac::ROLE_SUPER_ADMIN]);

        if (app()->environment('local')) {
            $this->call(FinanceDemoSeeder::class);
        }
    }
}
