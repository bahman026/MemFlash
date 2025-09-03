<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RolesEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    const string ADMIN_EMAIL = 'admin@memora.dev';

    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            [
                'email' => self::ADMIN_EMAIL,
            ],
            [
                'name' => 'john doe',
                'email_verified_at' => now(),
                'password' => 'password',
                'status' => UserStatusEnum::ACTIVE->value,
            ]
        );
    }
}
