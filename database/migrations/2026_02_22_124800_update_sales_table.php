<?php
// database/migrations/2026_02_24_000012_update_sales_table.php

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
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->enum('payment_status', ['pending', 'paid'])->default('pending')->after('notes');
            $table->enum('payment_method', ['cash', 'transfer'])->nullable()->after('payment_status');
            $table->decimal('cash_received', 12, 2)->nullable()->after('payment_method');
            $table->decimal('change', 12, 2)->nullable()->after('cash_received');
            $table->foreignId('processed_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable()->after('processed_by');
            $table->timestamp('completed_at')->nullable()->after('processed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['processed_by']);
            $table->dropColumn([
                'order_id',
                'payment_status',
                'payment_method',
                'cash_received',
                'change',
                'processed_by',
                'processed_at',
                'completed_at'
            ]);
        });
    }
};
