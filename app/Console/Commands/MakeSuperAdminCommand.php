<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeSuperAdminCommand extends Command
{
    protected $signature = 'superadmin:make
                            {username : The username of the user}
                            {--password= : Password (required when creating a new user)}
                            {--email= : Email (optional when creating; defaults to username@localhost)}';

    protected $description = 'Create a Store Room Supervisor account or promote an existing user to Store Room Supervisor';

    public function handle(): int
    {
        $username = $this->argument('username');

        $user = User::query()->where('username', $username)->first();

        if ($user) {
            $user->role = 'Store Room Supervisor';
            $user->save();
            $this->info("User [{$username}] is now a Store Room Supervisor. You can log in with this username.");

            return self::SUCCESS;
        }

        $password = $this->option('password');
        if (empty($password)) {
            $password = $this->secret('Enter a password for the new Store Room Supervisor');
            if (empty($password)) {
                $this->error('A password is required to create a new user.');

                return self::FAILURE;
            }
        }

        $email = $this->option('email') ?: $username.'@localhost';
        $name = ucfirst(strtolower(preg_replace('/[^a-z0-9]/i', ' ', $username))) ?: 'Store Room Supervisor';

        User::query()->create([
            'name' => $name,
            'email' => $email,
            'username' => $username,
            'password' => $password,
            'role' => 'Store Room Supervisor',
            'created_by' => 'CLI',
        ]);

        $this->info("Store Room Supervisor account created for [{$username}]. You can now log in with this username and password.");

        return self::SUCCESS;
    }
}
