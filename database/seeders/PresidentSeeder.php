<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PresidentSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('PRESIDENT_EMAIL', 'president@ksu.test');
        $name = env('PRESIDENT_NAME', 'University President');
        $password = env('PRESIDENT_PASSWORD');

        $user = User::firstOrNew(['email' => $email]);

        $user->name = $name;
        $user->role = User::ROLE_PRESIDENT;
        $user->status = User::STATUS_ACTIVE;

        $shouldSetPassword = ! $user->exists || ($password !== null && $password !== '');

        if ($shouldSetPassword) {
            $passwordToSet = $password !== null && $password !== '' ? $password : 'password';
            $user->password = Hash::make($passwordToSet);
        }

        if (! $user->email_verified_at) {
            $user->email_verified_at = now();
        }

        $user->save();
    }
}
