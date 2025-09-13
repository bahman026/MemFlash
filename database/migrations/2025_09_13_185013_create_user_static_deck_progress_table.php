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
        Schema::create('user_static_deck_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('static_deck_id')->constrained()->onDelete('cascade');
            $table->integer('cards_studied')->default(0);
            $table->integer('total_cards')->default(0);
            $table->timestamp('last_studied_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('progress_data')->nullable(); // for storing detailed progress
            $table->timestamps();

            // Ensure one progress record per user per static deck
            $table->unique(['user_id', 'static_deck_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_static_deck_progress');
    }
};
