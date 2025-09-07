<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    const string ADMIN_EMAIL = 'admin@memflash.dev'; // Default fallback

    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            [
                'email' => env('ADMIN_EMAIL', self::ADMIN_EMAIL),
            ],
            [
                'name' => 'john doe',
                'email_verified_at' => now(),
                'password' => env('ADMIN_PASSWORD', 'password'),
                'status' => UserStatusEnum::ACTIVE->value,
            ]
        );
    }
}
