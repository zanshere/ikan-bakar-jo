<?php
// database/migrations/2026_02_05_000007_create_restock_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restock_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restock_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained();
            $table->decimal('quantity', 10, 2);
            $table->decimal('price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restock_items');
    }
};
