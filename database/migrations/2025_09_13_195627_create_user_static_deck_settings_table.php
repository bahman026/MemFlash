<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_static_deck_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('static_deck_id')->constrained()->onDelete('cascade');
            $table->integer('cards_per_day')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'static_deck_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_static_deck_settings');
    }
};
