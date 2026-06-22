<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {username} {email} {password}';
    protected $description = 'Membuat akun admin melalui CI/CD pipeline';

    public function handle()
    {
        $username = $this->argument('username');
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Administrator',
                'username' => $username,
                'password' => Hash::make($password),
                'role' => 'admin',
            ]
        );

        $this->info("Admin '{$username}' berhasil dibuat/diperbarui!");
    }
}