<?php
// database/migrations/2026_02_24_000004_add_sauce_id_to_sale_items_table.php

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
        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('sauce_id')->nullable()->after('menu_id')->constrained('menus')->nullOnDelete();
            $table->decimal('additional_price', 10, 2)->default(0)->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['sauce_id']);
            $table->dropColumn(['sauce_id', 'additional_price']);
        });
    }
};
