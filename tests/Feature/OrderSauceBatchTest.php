<?php

use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('reduces sauce ingredient stock only when cumulative accepted orders reach a new batch of five portions', function () {
    $user = User::create([
        'name' => 'User Test',
        'email' => 'user@test.local',
        'password' => bcrypt('password'),
        'role' => 'owner',
    ]);

    $menuIngredient = Ingredient::create([
        'code' => 'ING-MENU-001',
        'name' => 'Ikan Segar',
        'unit' => 'kg',
        'stock' => 100,
        'min_stock' => 1,
        'price' => 10000,
    ]);

    $sauceIngredient = Ingredient::create([
        'code' => 'ING-SAUCE-001',
        'name' => 'Bumbu Saus',
        'unit' => 'kg',
        'stock' => 10,
        'min_stock' => 1,
        'price' => 5000,
    ]);

    $mainMenu = Menu::create([
        'code' => 'MENU-MAIN-001',
        'name' => 'Kepiting',
        'type' => 'main',
        'price' => 50000,
        'is_active' => true,
    ]);

    $sauceMenu = Menu::create([
        'code' => 'MENU-SAUCE-001',
        'name' => 'Saus Tiram',
        'type' => 'sauce',
        'price' => 0,
        'is_active' => true,
    ]);

    $mainMenu->ingredients()->attach($menuIngredient->id, ['quantity' => 1]);
    $sauceMenu->ingredients()->attach($sauceIngredient->id, ['quantity' => 1]);

    $firstOrder = Order::create([
        'user_id' => $user->id,
        'status' => 'pending',
        'total' => 200000,
    ]);

    OrderItem::create([
        'order_id' => $firstOrder->id,
        'menu_id' => $mainMenu->id,
        'sauce_id' => $sauceMenu->id,
        'quantity' => 4,
        'price' => 50000,
        'additional_price' => 0,
        'subtotal' => 200000,
    ]);

    $firstOrder->load('items.menu.ingredients', 'items.sauce.ingredients');
    $firstOrder->accept($user->id);

    expect((float) $sauceIngredient->fresh()->stock)->toBe(10.0)
        ->and((float) $menuIngredient->fresh()->stock)->toBe(96.0);

    $secondOrder = Order::create([
        'user_id' => $user->id,
        'status' => 'pending',
        'total' => 50000,
    ]);

    OrderItem::create([
        'order_id' => $secondOrder->id,
        'menu_id' => $mainMenu->id,
        'sauce_id' => $sauceMenu->id,
        'quantity' => 1,
        'price' => 50000,
        'additional_price' => 0,
        'subtotal' => 50000,
    ]);

    $secondOrder->load('items.menu.ingredients', 'items.sauce.ingredients');
    $secondOrder->accept($user->id);

    expect((float) $sauceIngredient->fresh()->stock)->toBe(9.0)
        ->and((float) $menuIngredient->fresh()->stock)->toBe(95.0);
});
