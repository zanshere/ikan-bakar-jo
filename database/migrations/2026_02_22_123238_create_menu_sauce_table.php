<?php
// database/migrations/2026_02_24_000002_create_menu_sauce_table.php

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
        Schema::create('menu_sauce', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->onDelete('cascade');
            $table->foreignId('sauce_id')->constrained('menus')->onDelete('cascade');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['menu_id', 'sauce_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_sauce');
    }
};
