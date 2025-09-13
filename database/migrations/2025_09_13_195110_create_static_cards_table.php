<?php

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
        Schema::create('static_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('static_deck_id')->constrained()->onDelete('cascade');
            $table->string('front');
            $table->string('back');
            $table->json('audio')->nullable();
            $table->integer('interval')->default(1);
            $table->decimal('ease_factor', 3, 2)->default(2.5);
            $table->integer('repetitions')->default(0);
            $table->timestamp('revised_at')->nullable();
            $table->timestamp('last_reviewed')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('static_cards');
    }
};
