<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Rbac;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CreateUserCommand extends Command
{
    protected $signature = 'app:create-user
                            {--name= : Full name}
                            {--username= : Unique username}
                            {--email= : Email address}
                            {--role=* : Role(s) to assign}';

    protected $description = 'Create a user and assign panel roles (registration is disabled)';

    public function handle(): int
    {
        $name = $this->option('name') ?: text('Full name', required: true);
        $username = $this->option('username') ?: text('Username', required: true);
        $email = $this->option('email') ?: text('Email', required: true);

        $validator = Validator::make(compact('name', 'username', 'email'), [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $roles = $this->option('role')
            ?: multiselect(
                label: 'Assign roles',
                options: Role::pluck('name', 'name')->all(),
                hint: 'Space to select, enter to confirm',
            );

        $unknown = array_diff($roles, Role::pluck('name')->all());
        if ($unknown !== []) {
            $this->error('Unknown role(s): '.implode(', ', $unknown));

            return self::FAILURE;
        }

        $secret = password('Password', required: true);

        $user = User::create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $secret,
        ]);

        $user->syncRoles($roles);

        $this->info("Created {$user->email} with role(s): ".(implode(', ', $roles) ?: '(none)'));

        if ($roles === []) {
            $this->warn('This user has no roles and cannot access any panel. Available: '.Rbac::ROLE_SUPER_ADMIN.', '.implode(', ', array_keys(Rbac::roles())));
        }

        return self::SUCCESS;
    }
}
