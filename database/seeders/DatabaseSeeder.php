<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $shouldSeedDev = ! User::query()->exists();

        $this->call([
            EmployeeCottageSeeder::class,
            PresidentSeeder::class,
            DevSeeder::class,
        ]);

        if ($shouldSeedDev && app()->environment(['local', 'testing', ])) {
            $this->call(DevSeeder::class);
        }
    }
}
