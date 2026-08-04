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
        $email = config('app.admin_email', self::ADMIN_EMAIL);

        $user = User::query()->firstOrCreate(
            [
                'email' => $email,
            ],
            [
                'name' => 'john doe',
                'password' => config('app.admin_password', 'password'),
            ]
        );

        // email_verified_at and status are not in User::$fillable, so passing them
        // to firstOrCreate() silently dropped them -- the admin was left
        // email-unverified and status fell back to the column default.
        if ($user->wasRecentlyCreated) {
            $user->forceFill([
                'email_verified_at' => now(),
                'status' => UserStatusEnum::ACTIVE->value,
            ])->save();
        }
    }
}
