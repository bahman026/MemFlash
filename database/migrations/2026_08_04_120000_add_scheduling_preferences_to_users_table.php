<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FSRS decides "same day" against a rollover hour in the user's own timezone,
     * not a naive 24-hour difference. That choice determines whether the same-day
     * stability formula runs, so it changes scheduling and has to be per user.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('timezone', 64)->default('UTC')->after('preferences');
            $table->unsignedTinyInteger('rollover_hour')->default(4)->after('timezone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['timezone', 'rollover_hour']);
        });
    }
};
