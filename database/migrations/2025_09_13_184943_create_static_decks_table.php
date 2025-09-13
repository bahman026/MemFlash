<?php

declare(strict_types=1);

use App\Enums\UserLevelEnum;
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
        Schema::create('static_decks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('level', array_column(UserLevelEnum::cases(), 'value'))->default(UserLevelEnum::STARTER->value);
            $table->integer('lesson_number')->nullable(); // lesson number within the level
            $table->string('category')->nullable(); // e.g., 'vocabulary', 'grammar', 'phrases'
            $table->string('language')->default('en'); // for future multi-language support
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0); // for ordering decks
            $table->json('metadata')->nullable(); // for additional data like tags, difficulty, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('static_decks');
    }
};
