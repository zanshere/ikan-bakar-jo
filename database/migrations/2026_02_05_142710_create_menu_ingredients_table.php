<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('menu_ingredient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 10, 2)->default(0);
            $table->timestamps();

            // Ensure unique combination of menu_id and ingredient_id
            $table->unique(['menu_id', 'ingredient_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu_ingredient');
    }
};
